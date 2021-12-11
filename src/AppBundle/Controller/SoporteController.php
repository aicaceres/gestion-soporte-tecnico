<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ConfigBundle\Controller\UtilsController;

use AppBundle\Entity\Requerimiento;
use AppBundle\Form\RequerimientoType;
use AppBundle\Entity\OrdenTrabajo;
use AppBundle\Entity\OrdenTrabajoDetalle;
use AppBundle\Form\OrdenTrabajoType;
use AppBundle\Form\OrdenTrabajoTareasType;
use AppBundle\Entity\EquipoUbicacion;
use AppBundle\Entity\Tarea;
use AppBundle\Form\TareaType;
use AppBundle\Entity\Mensajeria;

/**
 * @Route("/soporte")
 */
class SoporteController extends Controller
{
 /**
 * ORDENES DE TRABAJO
 */    

    /**
     * @Route("/new", name="soporte_ordentrabajo_new")
     * @Method("GET")
     * @Template("AppBundle:Soporte:ordentrabajo-edit.html.twig")
     */
    public function ordentrabajoNewAction()
    {
        UtilsController::haveAccess($this->getUser(),'ordentrabajo_new');
        $entity = new OrdenTrabajo();
        $entity->setFechaOrden(new \DateTime());
        $entity->setEstado('NUEVO');
        $form   = $this->ordentrabajoCreateForm($entity);
        $form->get('hora')->setData( $entity->getFechaOrden()->format('H:i') );
        $em = $this->getDoctrine()->getManager();
        $servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
        if($servTecnico){
            $form->get('dptoUbicacionNueva')->setData( $servTecnico->getId() );
            $form->get('pisoUbicacionNueva')->setData( $servTecnico->getPisos()[0]->getId() );
        }
        return array(
            'entity' => $entity,
            'servTecnico' => $servTecnico,
            'form'   => $form->createView(),
        );
    }    
    /**
     * @param OrdenTrabajo $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function ordentrabajoCreateForm(OrdenTrabajo $entity)
    {
        $form = $this->createForm(new OrdenTrabajoType(), $entity, array(
            'action' => $this->generateUrl('soporte_ordentrabajo_create'),
            'method' => 'PUT',
        ));
        return $form;
    }             
    /**
     * @Route("/", name="soporte_ordentrabajo_create")
     * @Method("PUT")
     * @Template("AppBundle:Soporte:ordentrabajo-edit.html.twig")
     */
    public function ordentrabajoCreateAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'ordentrabajo_new');
        $data = $request->get('appbundle_ordentrabajo');
        $ckEstado = $request->get('ckEstadoEquipo');
        $ckUbicacion = $request->get('ckubicacionEquipo');
        $entity = new OrdenTrabajo();
        $form = $this->ordentrabajoCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {            
                if ($ckEstado) {
                    $estado = $em->getRepository('ConfigBundle:Estado')->find($data['estadoEquipo']);
                }
                if ($ckUbicacion) {
                    $dpto = $em->getRepository('ConfigBundle:Departamento')->find($data['dptoUbicacionNueva']);
                    $piso = $em->getRepository('ConfigBundle:Piso')->find($data['pisoUbicacionNueva']);
                    $ubicacion = new EquipoUbicacion();
                    $ubicacion->setActual(true);
                    $ubicacion->setDepartamento($dpto);
                    $ubicacion->setEdificio($dpto->getEdificio());
                    $ubicacion->setUbicacion($dpto->getEdificio()->getUbicacion());
                    $ubicacion->setPiso($piso);
                    $ubicacion->setFechaEntrega(new \DateTime());
                }

                if( $data['requerimiento']){
                  $req = $em->getRepository('AppBundle:Requerimiento')->find($data['requerimiento']); 
                  $entity->setRequerimiento($req);
                  $req->setEstado('ASIGNADO');
                  $em->persist($req);
                }                
                foreach ($entity->getDetalles() as $key=> $detalle) {
                    $equipo = $em->getRepository('AppBundle:Equipo')->find($data['detalles'][$key]['equipoId']); 
                    $ubicOrignal = $equipo->getUbicacionActual();
                    $detalle->setEquipo($equipo); 
                    if( $data['detalles'][$key]['reqdetalleId'] ){
                       $reqDetalle = $em->getRepository('AppBundle:RequerimientoDetalle')->find($data['detalles'][$key]['reqdetalleId']);                        
                       $detalle->setRequerimientoDetalle($reqDetalle);
                       if( $reqDetalle->getEquipoUbicacionOriginal()  ){
                           $ubicOrignal = $reqDetalle->getEquipoUbicacionOriginal();
                       }else{
                           $reqDetalle->setEquipoUbicacionOriginal($ubicOrignal);
                           $em->persist($reqDetalle);                           
                       }
                    }
                    $detalle->setEquipoUbicacionOriginal($ubicOrignal);
                    if ($ckEstado) {
                        $equipo->setEstado($estado);
                    }
                    if ($ckUbicacion) {
                        $ubicOrignal->setActual(0);
                        $equipo->addUbicacion($ubicacion);
                    }
                    $em->persist($equipo);
                }
                $entity->setEstado('ABIERTO');   
                $entity->setFechaOrden( new \DateTime(UtilsController::toAnsiDate($data['fechaOrden']) .' '.$data['hora'].':00')  );
                $em->persist($entity);
                $em->flush();
                $entity->setNroOrden($entity->getId());
                $em->persist($entity);
                $em->flush();
                // generar mensajerÃ­a
                $textoMensaje = 'Se ha asignado la OT NÂ° '.str_pad($entity->getId(), 6, '0', STR_PAD_LEFT);;            
                $asunto = 'Nueva OT';                                    
                $mensaje = new Mensajeria();
                $mensaje->setDestinatario( $entity->getTecnico() );
                $mensaje->setAsunto($asunto);
                $mensaje->setMensaje($textoMensaje);
                $em->persist($mensaje);
                $em->flush();
                
                $this->addFlash('success', 'La orden fue creada!');
                return $this->redirectToRoute('soporte_ordentrabajo');
            } catch (\Exception $ex) {
               // $this->addFlash('danger', UtilsController::errorMessage($ex->getMessage()));
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return array(
            'entity' => $entity, 
            'form' => $form->createView(),
        );
    }    

    public function ordentrabajoEditAction($id)
    {
        UtilsController::haveAccess($this->getUser(),'ordentrabajo_edit');
        $em = $this->getDoctrine()->getManager();
        if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }
        $entity = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la orden de trabajo.');
        }
        if ($entity->getEstado() != 'ABIERTO' ) {
            $this->addFlash('danger','La orden de trabajo no estÃ¡ abierta para ediciÃ³n!');
            return $this->redirectToRoute('soporte_ordentrabajo');
        }
        $editForm = $this->ordentrabajoEditForm($entity);
        $editForm->get('hora')->setData( $entity->getFechaOrden()->format('H:i') );
        $deleteForm = $this->ordentrabajoDeleteForm($id);
        
        foreach ($entity->getDetalles() as $key=>$det) {     
            if($det->getEquipo()->getBarcode() ){
                $editForm->get('detalles')[$key]->get('barcode')->setData( $det->getEquipo()->getBarcode() );}
            $editForm->get('detalles')[$key]->get('equipoId')->setData( $det->getEquipo()->getId() );
            if( $det->getRequerimientoDetalle() ){
                $editForm->get('detalles')[$key]->get('reqdetalleId')->setData( $det->getRequerimientoDetalle()->getId() );
            }
        }        
        //$repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
        //$item = $em->find('AppBundle\Entity\Insumo', $id /*article id*/);
        //$logs = $repo->getLogEntries($item);
        $servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
        if($servTecnico){
            $editForm->get('dptoUbicacionNueva')->setData( $servTecnico->getId() );
            $editForm->get('pisoUbicacionNueva')->setData( $servTecnico->getPisos()[0]->getId() );
        }
        return array(
            'entity'      => $entity, 
            'servTecnico' => $servTecnico,
            'form'   => $editForm->createView(), 'delete_form' => $deleteForm->createView()
        );
    }    
    /**
    * @param OrdenTrabajo $entity The entity    
    * @return \Symfony\Component\Form\Form The form
    */
    private function ordentrabajoEditForm(OrdenTrabajo $entity)
    {
        $form = $this->createForm(new OrdenTrabajoType(), $entity, array(
            'action' => $this->generateUrl('soporte_ordentrabajo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }    
    /**
     * @Route("/{id}", name="soporte_ordentrabajo_update")
     * @Method("PUT")
     * @Template("AppBundle:Soporte:ordentrabajo-edit.html.twig")
     */
    public function ordentrabajoUpdateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(),'ordentrabajo_edit');
        $data = $request->get('appbundle_ordentrabajo');
        $em = $this->getDoctrine()->getManager();
        if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }
        $entity = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la orden de trabajo.');
        }
        $editForm = $this->ordentrabajoEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            try {    
                foreach ($entity->getDetalles() as $key=> $detalle) {
                    $equipo = $em->getRepository('AppBundle:Equipo')->find($data['detalles'][$key]['equipoId']); 
                    $detalle->setEquipo($equipo);
                    if( $data['detalles'][$key]['reqdetalleId'] ){
                       $reqDetalle = $em->getRepository('AppBundle:RequerimientoDetalle')->find($data['detalles'][$key]['reqdetalleId']); 
                       $detalle->setRequerimientoDetalle($reqDetalle);
                    }
                }    
                $entity->setFechaOrden( new \DateTime(UtilsController::toAnsiDate($data['fechaOrden']) .' '.$data['hora'].':00')  );
                $em->flush();
                return $this->redirectToRoute('soporte_ordentrabajo');
            } catch (\Exception $ex) {
                $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
            }
        }
        $deleteForm = $this->ordentrabajoDeleteForm($id);
        return array(
            'delete_form' => $deleteForm->createView(),
            'entity'      => $entity,
            'form'   => $editForm->createView()
        );
    }        
    
      
    

    


 
    
    
     
    /**
     * @Route("/ajax/changeTecnico", name="change_tecnico")
     * @Method("POST")
     */    
    public function changeTecnico(Request $request)
    {    
       $em = $this->getDoctrine()->getManager(); 
       try{
           $em->getConnection()->beginTransaction();
           $em->getConnection()->setAutoCommit(false);
            $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($request->get('id')); 
            $tecnico = $em->getRepository('ConfigBundle:Usuario')->find($request->get('tec')); 
            $tecant = $ot->getTecnico()->getNombre();
            $ot->setTecnico($tecnico );
            $em->persist($ot);
            $em->flush();
            // agregar nueva tarea con derivación
            $tarea = new Tarea();
            $hoy = new \DateTime();
            $tarea->setFecha( $hoy );
            $tarea->setOrdenTrabajo($ot);
            $tipo = $em->getRepository('ConfigBundle:TipoTarea')->findOneByAbreviatura('DS');
            $tarea->setTipoTarea($tipo);
            $tarea->setDescripcion('Se deriva la OT de '.$tecant.' a '.$ot->getTecnico()->getNombre());
            $em->persist($tarea);
            $em->flush();
            // generar mensajerí­a
            $textoMensaje = 'Se le ha derivado la OT N° '.str_pad($ot->getId(), 6, '0', STR_PAD_LEFT);;            
            $asunto = 'OT Derivada';                                    
            $mensaje = new Mensajeria();
            $mensaje->setDestinatario( $tecnico );
            $mensaje->setAsunto($asunto);
            $mensaje->setMensaje($textoMensaje);
            $em->persist($mensaje);            
            $em->flush();
            $session = $this->get('session');
            $session->set('otid', $ot->getId());
            $em->getConnection()->commit();
            return new Response('OK');
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            return new Response('No se pudo realizar la derivación');
        }        
    }
    
    /**
     * @Route("/terminarSoporte", name="terminar_soporte")
     * @Method("GET")
     */    
    public function terminarSoporte(Request $request)
    {    
       $em = $this->getDoctrine()->getManager(); 
       try{
            $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($request->get('id'));
            $ot->setEstado('CERRADO');
            $em->persist($ot);
            if ($ot->getRequerimiento()) {
                $req = $ot->getRequerimiento();
                $cerrar = true;
                foreach ($req->getTecnicosAsignados() as $otAsociada) {
                    if ($otAsociada->getEstado() != 'CERRADO') {
                        $cerrar = false;
                    }
                }
                if ($cerrar) {
                    $req->setEstado('FINALIZADO');
                    $em->persist($req);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Orden de trabajo cerrada!');
            return $this->redirectToRoute('soporte_ordentrabajo');
        } catch (\Exception $ex) {
            $this->addFlash('danger', 'No pudo realizarse la acciÃ³n. La orden continÃºa abierta.');
            return $this->redirectToRoute('soporte_ordentrabajo_tareas', array('id' => $request->get('id')));
        }        
    }
    
    /**
     * @Route("/addEquiposTarea", name="add_equipos_tarea")
     * @Method("POST")
     */    
    public function addEquiposTarea(Request $request)
    {    
       $em = $this->getDoctrine()->getManager(); 
       try{
            $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($request->get('id'));
            $list = $request->get('list');
            $cant = count($list);
            $rech = $exis = 0;
            $servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
            $estado = $em->getRepository('ConfigBundle:Estado')->findOneByAbreviatura('RP');
            $dpto = $em->getRepository('ConfigBundle:Departamento')->find($servTecnico->getId());
             $piso = $em->getRepository('ConfigBundle:Piso')->find($servTecnico->getPisos()[0]->getId());             
            foreach ($list as $item) {
                $equipo = $em->getRepository('AppBundle:Equipo')->find($item); 
                //Controlar que no se encuentre en otra OT abierta.                
                if( $equipo->getEnOrdenAbierta() ){
                    --$cant;
                    ++$rech;
                    continue;
                }
                $det = new OrdenTrabajoDetalle();
                $det->setEquipo($equipo);
                $det->setEquipoUbicacionOriginal($equipo->getUbicacionActual());
                $ot->addDetalle($det);
                $em->persist($ot);
                //
                $equipo->setEstado($estado);
                $equipo->getUbicacionActual()->setActual(0);
                $ubicacion = new EquipoUbicacion();
                $ubicacion->setActual(true);
                $ubicacion->setDepartamento($dpto);
                $ubicacion->setEdificio($dpto->getEdificio());
                $ubicacion->setUbicacion($dpto->getEdificio()->getUbicacion());
                $ubicacion->setPiso($piso);
                $ubicacion->setFechaEntrega(new \DateTime());
                $equipo->addUbicacion($ubicacion);
                $em->persist($equipo);
            }
            $em->flush();
            $msj = '';
            if( $cant>0 ){
                $msj = ($cant==1) ? 'Se agregÃ³ 1 equipo. ' : 'Se agregaron '.$cant.' equipos. ' ;                
            }
            if( $rech>0 ){
                $msjrech = ($rech==1) ? '1 equipo no se agregÃ³' :  $rech.' equipos no se agregaron';                
                $msj = $msj.$msjrech.' por estar en esta u otra OT abierta.';
            }
            $flash = ($cant==0) ? 'warning' : 'success';
            $this->addFlash($flash, $msj);
            return new Response('OK');
        } catch (\Exception $ex) {
            return new Response($ex->getMessage());
            return new Response('No se pudo realizar la derivaciÃ³n');
        }        
    }

    
/**
 * REQUERIMIENTOS
 */    
 



      


    

       
    
    


    /**
     * @Route("/findReqAbiertos", name="find_req_abiertos")
     * @Method("GET")
     */        
    public function findReqAbiertos()
    {
        $em = $this->getDoctrine()->getManager();
        $reqSinAsignar = $em->getRepository('AppBundle:Requerimiento')->findBy(array('estado'=>'SIN ASIGNAR'), array('fechaRequerimiento' => 'ASC'));        
        $reqAsignados = $em->getRepository('AppBundle:Requerimiento')->findBy(array('estado'=>'ASIGNADO'), array('fechaRequerimiento' => 'ASC'));        
        $html = $this->renderView('AppBundle:Requerimiento:partial-req-abiertos.html.twig', 
                array('sinAsignar' => $reqSinAsignar, 'asignados'=>$reqAsignados) );
        return new Response($html);
    }     
    /**
     * @Route("/findReqAsociado", name="find_req_asociado")
     * @Method("GET")
     */        
    public function findReqAsociado(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $requerimiento = $em->getRepository('AppBundle:Requerimiento')->find($id);
        $html = $this->renderView('AppBundle:Requerimiento:partial-requerimiento.html.twig', array('entity' => $requerimiento) );
        return new Response($html);
    }     
    /**
     * @Route("/findDetalleByReqId", name="find_detalle_by_reqid")
     * @Method("GET")
     */        
    public function findDetalleByReqId(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $requerimiento = $em->getRepository('AppBundle:Requerimiento')->find($id);
        $array = array();
        foreach ($requerimiento->getDetalles() as $det ){
            array_push( $array, array('id'=>$det->getId(),'eqId'=>$det->getEquipo()->getId(),
                'eqBc' => $det->getEquipo()->getBarcode(),
                'eqNombre'=>$det->getEquipo()->getTextoOT(), 'obs'=>$det->getDescripcion() ) );
        }
        $partial = $this->renderView('AppBundle:Requerimiento:partial-requerimiento-info.html.twig', array('entity' => $requerimiento) );
        return new Response( json_encode( array('equipos'=>$array, 'info'=>$partial)));
    }     
    
    
    
    
}