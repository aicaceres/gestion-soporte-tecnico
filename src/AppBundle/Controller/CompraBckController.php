<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;
//use Doctrine\Common\Collections\ArrayCollection;

use AppBundle\Entity\Compra;
use AppBundle\Form\CompraType;
use AppBundle\Entity\RecepcionCompra;
use AppBundle\Entity\RecepcionCompraDetalle;
use AppBundle\Form\RecepcionCompraType;

use AppBundle\Entity\Insumo;
use AppBundle\Entity\Equipo;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockHistorico;
use AppBundle\Entity\EquipoUbicacion;

/**
 * @Route("/compra")
 */
class CompraController extends Controller
{
    /**
     * @Route("/", name="compra_admin")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(),'compra');
        $em = $this->getDoctrine()->getManager();
        if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }
        $session = $this->get('session');          
        $sessionFiltro = $session->get('filtro_compras');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));  
                $filtro = array(
                    'proveedorId' => $request->get('proveedorId'),
                    'razonSocialId' => $request->get('razonSocialId'),
                    'solicitanteId' => $request->get('solicitanteId'),
                    'estado' => $request->get('estado'),
                    'desde' => $periodo['desde'],
                    'hasta' => $periodo['hasta'],
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $periodo = UtilsController::ultimoMesParaFiltro($sessionFiltro['desde'], $sessionFiltro['hasta']);  
                    $filtro = array(
                        'proveedorId' => $sessionFiltro['proveedorId'],
                        'razonSocialId' => $sessionFiltro['razonSocialId'],
                        'solicitanteId' => $sessionFiltro['solicitanteId'],
                        'estado' => $sessionFiltro['estado'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                } else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));  
                    $filtro = array('proveedorId' => 0,'razonSocialId' => 0,'solicitanteId' => 0,'estado' => '', 
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_compras', $filtro); 
        $proveedores = $em->getRepository('AppBundle:Proveedor')->findBy(array(), array('nombre' => 'ASC'));                       
        $razonSocial = $em->getRepository('ConfigBundle:Ubicacion')->findBy(array('razonSocial'=>1), array('nombre' => 'ASC')); 
        if($sessionFiltro['razonSocialId']){
            $solicitantes = $em->getRepository('ConfigBundle:Ubicacion')->findDptosByUbicacionId($razonSocialId); 
        }else{
            $solicitantes = null;
        }
                
      //  $filtro = array('razonSocialId'=>$razonSocialId, 'solicitanteId'=>$solicitanteId ,'estado'=>$request->get('estado'),
      //      'proveedorId'=>$proveedorId, 'desde'=>$periodo['desde'], 'hasta'=>$periodo['hasta']);
        $entities = $em->getRepository('AppBundle:Compra')->findByCriteria( $filtro);
       
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }
        return $this->render('AppBundle:Compra:index.html.twig', array(
            'entities' => $entities, 
            'proveedores' => $proveedores,
            'razonSocial' => $razonSocial,
            'solicitantes' => $solicitantes,
            'filtro' => $filtro,
            'deleteForms' => $deleteForms
        ));
    }  
    
    /**
     * @Route("/new", name="compra_admin_new")
     * @Method("GET")
     * @Template("AppBundle:Compra:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Compra();
        $entity->setFechaCompra(new \DateTime());
        $form   = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }        
    /**
     * @param Compra $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Compra $entity)
    {
        $form = $this->createForm(new CompraType(), $entity, array(
            'action' => $this->generateUrl('compra_create'),
            'method' => 'PUT',
        ));
        return $form;
    }    
    /**
     * @Route("/", name="compra_create")
     * @Method("PUT")
     * @Template("AppBundle:Compra:new.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(),'compra_new');
        $data = $request->get('appbundle_compra');        
        $em = $this->getDoctrine()->getManager();
        $new = $data['savenew'];
        $entity = new Compra();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        foreach ($entity->getDetalles() as $key => $detalle) {
            $row = $data['detalles'][$key];
            if ($row['insumoId']) {
                // es insumo existente                        
                $insumo = $em->getRepository('AppBundle:Insumo')->find($row['insumoId']);
                $detalle->setInsumo($insumo);
                $detalle->setCodigo($insumo->getBarcode());
            }
        }
        $procesar = false;
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager(); 
            try {
                $em->getConnection()->beginTransaction();   
                if( isset($data['enviado']) ){
                    $entity->setEstado('ENVIADO');
                    $entity->setFechaEnvioProveedor(new \DateTime());
                }else if(isset($data['recibido'])) {
                    $entity->setEstado('RECIBIDO');
                    $file = $request->files->get('appbundle_compra')['file'];
                    $procesar = true;
                }else{
                    $entity->setEstado('NUEVO');
                }
               /* foreach ( $entity->getDetalles()  as $key=>$detalle) {    
                    $row = $data['detalles'][$key]; 
                    if( $row['insumoId'] ){
                        // es insumo existente                        
                        $producto = $em->getRepository('AppBundle:Insumo')->find($row['insumoId']);
                        $detalle->setInsumo( $producto );
                    }
                }  */              
                /*
                foreach ( $entity->getDetalles()  as $key=>$detalle) {     
                    $row = $data['detalles'][$key];                    
                    if( $row['insumoId'] ){
                        // es insumo existente
                        $producto = $em->getRepository('AppBundle:Insumo')->find($row['insumoId']);
                    }else{
                        // crear insumo o equipo
                        $prod = $row['producto'];
                        $tipo = $em->getRepository('ConfigBundle:Tipo')->find($prod['tipo']);
                        if( $tipo->getClase()=='I' ){
                            $producto = new Insumo();
                        }else{
                            $producto = new Equipo();
                            $producto->setProveedor( $entity->getProveedor() );
                            $producto->setNroSerie( $prod['nroSerie'] );
                            $producto->setFechaCompra($entity->getFechaCompra());
                            $producto->setNroFactura($entity->getNroFactura());
                            $producto->setNroRemito($entity->getNroRemito());
                            $estado = $em->getRepository('ConfigBundle:Estado')->findOneByInicial(1);
                            $producto->setEstado($estado);     
                        }
                        $producto->setTipo($tipo);
                        $producto->setNombre($prod['nombre']);
                        $producto->setBarcode( $prod['barcode']);
                        $producto->setCodigo($prod['codigo']);
                        $producto->setMarca( $em->getRepository('ConfigBundle:Marca')->find($prod['selmarca']) );
                        $producto->setModelo( $em->getRepository('ConfigBundle:Modelo')->find($prod['selmodelo']) );                           
                    }                    
                    $em->persist($entity);
                    $em->flush();
                    if( $producto->getTipo()->getClase()=='I'){
                        $detalle->setInsumo( $producto );
                        // agregar stock
                        $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($producto->getId(), $deposito->getId());                    
                        if (!$stock) {
                            $stock = new Stock();
                            $stock->setInsumo($producto);
                            $stock->setDeposito($deposito);
                            $stock->setCantidad( 0 );
                        }
                        $stock->setCantidad( $stock->getCantidad() + $detalle->getCantidad() );
                        $em->persist($stock);
                        // Cargar movimiento
                        $movim = new StockHistorico();
                        $movim->setFecha($entity->getFechaCompra());
                        $movim->setTipo('COMPRA');
                        $movim->setSigno('+');
                        $movim->setMovimiento($entity->getId());
                        $movim->setInsumo($producto);
                        $movim->setCantidad($detalle->getCantidad());
                        $movim->setDeposito($deposito);
                        $em->persist($movim);
                    }else{
                        $detalle->setEquipo( $producto );
                        // agregar ubicacion
                        $ubicacion = new EquipoUbicacion();                                                
                        $ubicacion->setUbicacion($deposito->getEdificio()->getUbicacion());
                        $ubicacion->setEdificio($deposito->getEdificio());
                        $ubicacion->setDepartamento($deposito);
                        $ubicacion->setActual(TRUE);
                        $ubicacion->setFechaEntrega($entity->getFechaCompra());
                        $conceptoEntrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByInicial(1);
                        $ubicacion->setConceptoEntrega($conceptoEntrega);
                        $producto->addUbicacion($ubicacion);
                    }                                                           
                }     */

                $em->persist($entity);
                $em->flush();   
                 
                if($procesar){
                    /*
                     * CREAR EQUIPOS E INSUMOS NUEVOS Y AJUSTAR EL STOCK
                     */
                    if ($this->recepcionDeCompra($em,$entity,null,$file)){
                        // marcar la compra como recibida.
                        $entity->setEstado('RECIBIDO');
                        foreach ($entity->getDetalles() as $detalle) {
                            $detalle->setRecibido( $detalle->getCantidad() );
                        }
                        $em->persist($entity);
                        $em->flush();
                    }
                }
                $em->getConnection()->commit();
               
                if ($new == 'S') {
                    $this->addFlash('success', 'La compra fue creada. Puede crear la siguiente!');
                    return $this->redirectToRoute('compra_admin_new');
                } else {
                    $this->addFlash('success', 'La compra fue creada!');
                    return $this->redirectToRoute('compra_admin');
                }
            } catch (\Exception $ex) {  
               $em->getConnection()->rollback();
               $this->addFlash('danger',$ex->getMessage() ); 
               // $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
            }
        }
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }     
    
    /**
     * @Route("/{id}/edit", name="compra_admin_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(),'compra_edit');
        $em = $this->getDoctrine()->getManager();
        if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }
        $entity = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la compra.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        
        //$repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
        //$item = $em->find('AppBundle\Entity\Insumo', $id /*article id*/);
        //$logs = $repo->getLogEntries($item);
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        );
    }
    /**
    * @param Compra $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Compra $entity)
    {
        $form = $this->createForm(new CompraType(), $entity, array(
            'action' => $this->generateUrl('compra_admin_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    /**
     * @Route("/{id}", name="compra_admin_update")
     * @Method("PUT")
     * @Template("AppBundle:Compra:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
      /*  if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }*/
        $entity = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la compra.');
        }
        $data = $request->get('appbundle_compra');
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
      /*  foreach ($entity->getDetalles() as $det){
            var_dump($det->getId());
          /*  $item = array('id'=>$det->getId(), 'insumo'=> ($det->getInsumo())?$det->getInsumo()->getId():0, 
                'equipo'=> ($det->getEquipo())?$det->getEquipo()->getId():0,
                'cantidad'=>$det->getCantidad(), 'precio'=> $det->getPrecio()  );
            array_push($original, $item);
        }*/
           
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                if( isset($data['enviado']) ){
                    $entity->setEstado('ENVIADO');
                    $entity->setFechaEnvioProveedor(new \DateTime());
                }
                foreach ( $entity->getDetalles()  as $key=>$detalle) {    
                    $row = $data['detalles'][$key]; 
                    if( is_null($detalle->getId()) && !is_null($row['insumoId']) ){
                        // es insumo existente                        
                        $producto = $em->getRepository('AppBundle:Insumo')->find($row['insumoId']);
                        $detalle->setInsumo( $producto );
                    }
                }   
                
             /*       
                foreach($entity->getDetalles() as $key=>$detalle){
                    $tipo = ($detalle->getInsumo())?'I':'E';
                    if( $detalle->getId() ){
                        // ya existía.. revisar con anterior para aplicar cambios
                        foreach( $original as $key=> $orig ){
                            if($detalle->getId()==$orig['id']){                                  
                                if($tipo=='I'){                                                                      
                                    if($cambioDeposito){
                                        // restar en anterior
                                        $stockant = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($orig['insumo'], $depant); 
                                        $stockant->setCantidad( $stockant->getCantidad() - $orig['cantidad'] );
                                        $em->persist($stockant);
                                        // asentar movimiento
                                        $movim1 = new StockHistorico();
                                        $movim1->setFecha($entity->getFechaCompra());
                                        $movim1->setTipo('COMPRA');
                                        $movim1->setSigno('-');
                                        $movim1->setMovimiento($entity->getId());
                                        $movim1->setInsumo($detalle->getInsumo());
                                        $movim1->setCantidad($orig['cantidad']);
                                        $depositoAnterior = $em->getRepository('ConfigBundle:Departamento')->find($depant);
                                        $movim1->setDeposito($depositoAnterior);
                                        $em->persist($movim1);
                                        // agregar en nuevo
                                        $stocknew = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($detalle->getInsumo()->getId(), $deposito->getId());
                                        if (!$stocknew) {
                                            $stocknew = new Stock();
                                            $stocknew->setInsumo($detalle->getInsumo());
                                            $stocknew->setDeposito($deposito);
                                            $stocknew->setCantidad( 0 );
                                        }
                                        $stocknew->setCantidad( $stocknew->getCantidad() + $detalle->getCantidad() );
                                        $em->persist($stocknew);
                                        $em->flush();
                                        // asentar movimiento
                                        $movim2 = new StockHistorico();
                                        $movim2->setFecha($entity->getFechaCompra());
                                        $movim2->setTipo('COMPRA');
                                        $movim2->setSigno('+');
                                        $movim2->setMovimiento($entity->getId());
                                        $movim2->setInsumo($detalle->getInsumo());
                                        $movim2->setCantidad($detalle->getCantidad());                                        
                                        $movim2->setDeposito($deposito);
                                        $em->persist($movim2);
                                    }else{
                                        $dif = $detalle->getCantidad() - $orig['cantidad'];
                                        // modificar si cambia la cantidad
                                        if($dif!=0){
                                          $stock1 = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($detalle->getInsumo()->getId(), $deposito->getId()); 
                                          $stock1->setCantidad( $stock1->getCantidad() + $dif );
                                          $em->persist($stock1);
                                          // asentar movimiento
                                            $movim3 = new StockHistorico();
                                            $movim3->setFecha($entity->getFechaCompra());
                                            $movim3->setTipo('COMPRA');
                                            $movim3->setSigno( ($dif>0)?'+':'-'  );
                                            $movim3->setMovimiento($entity->getId());
                                            $movim3->setInsumo($detalle->getInsumo());
                                            $movim3->setCantidad( abs($dif) );
                                            $movim3->setDeposito($deposito);
                                            $em->persist($movim3);
                                        }
                                    }
                                }else{
                                    if($cambioDeposito){
                                        // cambiar ubicación del equipo
                                        $ubic = $detalle->getEquipo()->getUbicacionActual();
                                        $ubic->setDepartamento( $deposito );
                                        $ubic->setEdificio( $deposito->getEdificio() );
                                        $ubic->setUbicacion( $deposito->getEdificio()->getUbicacion() );
                                    }
                                }
                                // procesado, eliminar del array
                                unset($original[$key]);
                            }                         
                        }
                    }else{
                        // AGREGAR NUEVO ITEM
                        $row = $data['detalles'][$key];                    
                        if( $row['insumoId'] ){
                            // es insumo existente
                            $producto = $em->getRepository('AppBundle:Insumo')->find($row['insumoId']);
                        }else{
                            // crear insumo o equipo
                            $prod = $row['producto'];
                            $tipo = $em->getRepository('ConfigBundle:Tipo')->find($prod['tipo']);
                            if( $tipo->getClase()=='I' ){
                                $producto = new Insumo();
                            }else{
                                $producto = new Equipo();
                                $producto->setProveedor( $entity->getProveedor() );
                                $producto->setNroSerie( $prod['nroSerie'] );
                                $producto->setFechaCompra($entity->getFechaCompra());
                                $producto->setNroFactura($entity->getNroFactura());
                                $producto->setNroRemito($entity->getNroRemito());
                                $estado = $em->getRepository('ConfigBundle:Estado')->findOneByInicial(1);
                                $producto->setEstado($estado);     
                            }
                            $producto->setTipo($tipo);
                            $producto->setNombre($prod['nombre']);
                            $producto->setBarcode( $prod['barcode']);
                            $producto->setCodigo($prod['codigo']);
                            $producto->setMarca( $em->getRepository('ConfigBundle:Marca')->find($prod['selmarca']) );
                            $producto->setModelo( $em->getRepository('ConfigBundle:Modelo')->find($prod['selmodelo']) ); 
                            $em->persist($entity);
                            $em->flush();
                        }
                        
                        if( $producto->getTipo()->getClase()=='I'){
                            $detalle->setInsumo( $producto );
                            // agregar stock
                            $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($producto->getId(), $deposito->getId());                    
                            if (!$stock) {                                
                                $stock = new Stock();
                                $stock->setInsumo($producto);
                                $stock->setDeposito($deposito);
                                $stock->setCantidad( 0 );
                            }
                            $stock->setCantidad( $stock->getCantidad() + $detalle->getCantidad() );
                            $em->persist($stock);
                            $em->flush();
                            // Cargar movimiento
                            $movim = new StockHistorico();
                            $movim->setFecha($entity->getFechaCompra());
                            $movim->setTipo('COMPRA');
                            $movim->setSigno('+');
                            $movim->setMovimiento($entity->getId());
                            $movim->setInsumo($producto);
                            $movim->setCantidad($detalle->getCantidad());
                            $movim->setDeposito($deposito);
                            $em->persist($movim);
                        }else{
                            $detalle->setEquipo( $producto );
                            // agregar ubicacion
                            $ubicacion = new EquipoUbicacion();                                                
                            $ubicacion->setUbicacion($deposito->getEdificio()->getUbicacion());
                            $ubicacion->setEdificio($deposito->getEdificio());
                            $ubicacion->setDepartamento($deposito);
                            $ubicacion->setActual(TRUE);
                            $ubicacion->setFechaEntrega($entity->getFechaCompra());
                            $conceptoEntrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByInicial(1);
                            $ubicacion->setConceptoEntrega($conceptoEntrega);
                            $producto->addUbicacion($ubicacion);
                        }
                    }
                                                           
                }
                if(count($original)>0){
                    // si quedan elementos deshacer acciones. 
                    // Insumo descontar del stock original, Equipo eliminar. 
                    foreach( $original as $key=> $orig ){
                        if( $orig['insumo'] ){
                            // restar en anterior
                            $stocki = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($orig['insumo'], $depant); 
                            $stocki->setCantidad( $stocki->getCantidad() - $orig['cantidad'] );
                            $em->persist($stocki);
                            // asentar movimiento
                            $movimi = new StockHistorico();
                            $movimi->setFecha($entity->getFechaCompra());
                            $movimi->setTipo('COMPRA');
                            $movimi->setSigno('-');
                            $movimi->setMovimiento($entity->getId());
                            $insumo = $em->getRepository('AppBundle:Insumo')->find($orig['insumo']);
                            $movimi->setInsumo($insumo);
                            $movimi->setCantidad($orig['cantidad']);
                            $depositoAnterior = $em->getRepository('ConfigBundle:Departamento')->find($depant);
                            $movimi->setDeposito($depositoAnterior);
                            $em->persist($movimi);
                        }else{
                            $eq = $em->getRepository('AppBundle:Equipo')->find($orig['equipo']); ;
                            $em->remove($eq);
                        }
                    }
                }       */        
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirectToRoute('compra_admin');
            } catch (\Exception $ex) {
                $em->getConnection()->rollback();
                echo $ex->getMessage();
                die;
                //$this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
            }
        }
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView()
        );
    }    
    
    
    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('compra_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }     
   /**
     * @Route("/delete/{id}", name="compra_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'compra_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity = $em->getRepository('AppBundle:Compra')->find($id);
                if (!$entity) {
                    throw $this->createNotFoundException('No existe la compra.');
                }
                if (is_null($entity->getDeletedAt())) {
                    //forzar el guardado de ultima fecha de modificación antes de softdelete
                    $em->getFilters()->enable('softdeleteable');
                    $entity->setUpdated(new \DateTime());
                    $em->persist($entity);
                    $em->flush();
                }
                $em->remove($entity);
                $em->flush();
                $em->getConnection()->commit();
                $this->addFlash('success', 'La compra fue eliminada!');
            } catch (\Exception $ex) {
                 $em->getConnection()->rollback();
                $this->addFlash('danger',UtilsController::errorMessage($ex->getMessage()) ); 
            }
        }

        return $this->redirectToRoute('compra_admin');
    }
    
    
    /**
     * @Route("/{id}/show", name="compra_admin_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        UtilsController::haveAccess($this->getUser(),'compra');      
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro de compra.');
        }
        return $this->render('AppBundle:Compra:show.html.twig', array(
            'entity'      => $entity, ));
    }  

    /**
     * @Route("/{id}/recepcion", name="compra_admin_recepcion")
     * @Method("GET")
     * @Template()
     */
    public function recepcionAction($id)
    {
        UtilsController::haveAccess($this->getUser(),'compra_recepcion');
        $em = $this->getDoctrine()->getManager();        
        $compra = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$compra) {
            throw $this->createNotFoundException('No se encuentra la compra.');
        }
        $recepcion = new RecepcionCompra();
        $recepcion->setFechaRecepcion( new \DateTime() );
        foreach ($compra->getDetalles() as $det) {
            if( $det->isPendiente() ){
                if( $det->getTipo()->getClase()=='E' && $det->getCantidadPendiente()>1 ){
                    // Si es + de un equipo corregir y dividir en la cantidad
                    $detrec = new RecepcionCompraDetalle();
                    $detrec->setCompraDetalle($det);
                    $detrec->setCantidad( 1 );                             
                    $detrec->setPrecio( $det->getPrecio() );                
                    $recepcion->addDetalle($detrec); 
                    $cant = $det->getCantidadPendiente() - 1;
                    for ($x = $cant; $x > 0; $x--) {
                       $detrec = new RecepcionCompraDetalle();
                        $detrec->setCompraDetalle($det);
                        $detrec->setCantidad( 1 );                                                     
                        $detrec->setPrecio( $det->getPrecio() );                
                        $recepcion->addDetalle($detrec); 
                    } 
                }else{
                    $detrec = new RecepcionCompraDetalle();
                    $detrec->setCompraDetalle($det);
                    $detrec->setCantidad( $det->getCantidadPendiente() );                
                    $detrec->setPrecio( $det->getPrecio() );                
                    $recepcion->addDetalle($detrec);                    
                }
            }
        }
   
        $compra->addRecepcion($recepcion);
        $form = $this->createForm(new RecepcionCompraType(), $recepcion, array(
            'action' => $this->generateUrl('compra_recepcion_create', array('id' => $compra->getId())),
            'method' => 'PUT',
        ));
      /*  foreach ($recepcion->getDetalles() as $key=>$det) {            
            $form->get('detalles')[$key]->get('precio')->setData( $det->getCompraDetalle()->getPrecio() );
        }     */   
        return array(
            'entity'      => $compra,
            'form'   => $form->createView()
        );
    }    

    /**
     * @Route("{id}/recepcion", name="compra_recepcion_create")
     * @Method("PUT")
     * @Template("AppBundle:Compra:recepcion.html.twig")
     */
    public function createRecepcionAction(Request $request,$id)
    {
        UtilsController::haveAccess($this->getUser(),'compra_recepcion');
        $em = $this->getDoctrine()->getManager();
          $recepcion = new RecepcionCompra();
        $form = $this->createForm(new RecepcionCompraType(), $recepcion, array(
            'action' => $this->generateUrl('compra_recepcion_create', array('id' => $id)),
            'method' => 'PUT',
        ));
        $compra = $em->getRepository('AppBundle:Compra')->find($id);
        $form->handleRequest($request);
        if ($form->isValid()) {            
            try {
                $em->getConnection()->beginTransaction();
                $em->getConnection()->setAutoCommit(false);
                if( is_null($compra->getNroFactura()) && !is_null($recepcion->getNroFactura())  )
                    $compra->setNroFactura($recepcion->getNroFactura());
                $formCompraDetalle = $request->get('appbundle_recepcioncompra')['detalles'];
                foreach ( $recepcion->getDetalles()  as $key=>$detalle) {  
                    if($detalle->getCantidad()>0){
                        $compdet = $detalle->getCompraDetalle();
                        $compdet->setRecibido( $compdet->getRecibido() + $detalle->getCantidad() );
                        $compdet->setNroSerie( $formCompraDetalle[$key]['nroSerie'] );
                        $compdet->setPrecio( $detalle->getPrecio() );
                        $em->persist($compdet);
                        $em->flush();
                    }else{
                        $recepcion->removeDetalle($detalle);
                    }                    
                } 
                $recepcion->setCompra($compra);
                $estado = ( $compra->isCompleto() ) ? 'RECIBIDO' : 'RECEPCION PARCIAL';
                $compra->setEstado( $estado );
                $em->persist($compra);
                $em->persist($recepcion);
                $em->flush();                
                $this->recepcionDeCompra($em,$compra,$recepcion);
                $em->getConnection()->commit();
                
                $this->addFlash('success', 'Se ha registrado la recepción!');
                return $this->redirectToRoute('compra_admin');                
            } catch (\Exception $ex) {  
               $em->getConnection()->rollback();
                return $this->addFlash('danger',$ex->getMessage() ); 
            }
        }        
        return array(
            'entity' => $compra,
            'form'   => $form->createView(),
        );
    }         
    
    private function recepcionDeCompra($em, $compra, $recepcion = NULL, $file = NULL) {
        try {
            //$em->getConnection()->beginTransaction();
            if (is_null($recepcion)) {
                /*
                 * Si es nuevo crear una recepción antes de procesar
                 */
                $recepcion = new RecepcionCompra();
                $recepcion->setFechaRecepcion($compra->getFechaCompra());
                $recepcion->setNroFactura($compra->getNroFactura());
                $recepcion->setNroRemito($compra->getNroRemito());
                $recepcion->setFile($file);
                $recepcion->setCompra($compra);
                foreach ($compra->getDetalles() as $det) {
                    if ($det->isPendiente()) {
                        $detrec = new RecepcionCompraDetalle();
                        $detrec->setCompraDetalle($det);
                        $detrec->setCantidad($det->getCantidad());
                        $recepcion->addDetalle($detrec);
                    }
                }
                $em->persist($recepcion);
                $em->flush();
            }
            /*
             * CREAR EQUIPO E INSUMOS y ACTUALIZAR STOCK
             */            
            foreach ($recepcion->getDetalles() as $detalle) {
                $compraDetalle = $detalle->getCompraDetalle(); 
                $deposito = $em->getRepository('ConfigBundle:Departamento')->findOneByInicial(1);
                if($compraDetalle->getInsumo()){
                    // es insumo existente
                    $producto = $em->getRepository('AppBundle:Insumo')->find( $compraDetalle->getInsumo()->getId() );
                    $tipo = $producto->getTipo();
                }else{
                    // crear insumo o equipo
                    $tipo = $em->getRepository('ConfigBundle:Tipo')->find($compraDetalle->getTipo());
                    if( $tipo->getClase()=='I' ){
                        $producto = new Insumo();
                    }else{
                        $producto = new Equipo();
                        $producto->setProveedor( $compra->getProveedor() );
                        $producto->setNroSerie( $compraDetalle->getNroSerie() );
                        $producto->setFechaCompra($compra->getFechaCompra());
                        $producto->setNroFactura($compra->getNroFactura());
                        $oc = ($compra->getRazonSocial()) ?
                            $compra->getRazonSocial()->getAbreviatura().'/'.$compra->getOrdenCompra() :
                            $compra->getOrdenCompra();
                        $producto->setNroOrdenCompra($oc);
                        $producto->setNroRemito($recepcion->getNroRemito());
                        $estado = $em->getRepository('ConfigBundle:Estado')->findOneByInicial(1);
                        $producto->setEstado($estado);     
                    }
                    $producto->setTipo($tipo);
                    $producto->setNombre($compraDetalle->getNombre());                    
                    $producto->setCodigo($compraDetalle->getCodigo());
                    $producto->setMarca( $em->getRepository('ConfigBundle:Marca')->find($compraDetalle->getItemMarca()) );
                    $producto->setModelo( $em->getRepository('ConfigBundle:Modelo')->find($compraDetalle->getItemModelo()) );
                    $em->persist($producto);
                    $em->flush();                    
                    if( $tipo->getClase()=='E' ){
                        $compraDetalle->setEquipo($producto);
                        $em->persist($compraDetalle);
                        $producto->setBarcode(  str_pad($producto->getTipo()->getId(),3,'0',STR_PAD_LEFT) .
                        str_pad($producto->getMarca()->getId(),3,'0',STR_PAD_LEFT) .str_pad($producto->getModelo()->getId(),3,'0',STR_PAD_LEFT).
                        str_pad($producto->getId(),5,'0',STR_PAD_LEFT) );                        
                        // agregar ubicacion                        
                        $ubicacion = new EquipoUbicacion();                                                
                        $ubicacion->setUbicacion($deposito->getEdificio()->getUbicacion());
                        $ubicacion->setEdificio($deposito->getEdificio());
                        $ubicacion->setDepartamento($deposito);
                        $ubicacion->setPiso($deposito->getPisos()[0]);
                        $ubicacion->setActual(TRUE);
                        $ubicacion->setFechaEntrega($compra->getFechaCompra());
                        $conceptoEntrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByInicial(1);
                        $ubicacion->setConceptoEntrega($conceptoEntrega);
                        $producto->addUbicacion($ubicacion);
                        $em->persist($producto);
                        $em->flush();
                    }
                }
                if( $tipo->getClase()=='I' ){                    
                   // Ajustar stock 
                    $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($producto->getId(), $deposito->getId());                    
                    if (!$stock) {
                            $stock = new Stock();
                            $stock->setInsumo($producto);
                            $stock->setDeposito($deposito);
                            $stock->setCantidad( 0 );
                        }
                    $stock->setCantidad( $stock->getCantidad() + $detalle->getCantidad() );
                    $em->persist($stock);
                    $em->flush();
                    // Cargar movimiento
                    $movim = new StockHistorico();
                    $movim->setFecha($recepcion->getFechaRecepcion());
                    $movim->setTipo('COMPRA');
                    $movim->setSigno('+');
                    $movim->setMovimiento($compra->getId());
                    $movim->setInsumo($producto);
                    $movim->setCantidad($detalle->getCantidad());
                    $movim->setDeposito($deposito);
                    $em->persist($movim);    
                    $em->flush();
                }
            }
            
           // $em->getConnection()->commit();
            return true;
        } catch (\Exception $ex) {
         //   $em->getConnection()->rollback();            
            return false;
        }
    }

}