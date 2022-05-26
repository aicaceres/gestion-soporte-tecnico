<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Compra;
use AppBundle\Form\CompraType;
use AppBundle\Form\CompraEditType;
use AppBundle\Entity\RecepcionCompra;
use AppBundle\Entity\RecepcionCompraDetalle;
use AppBundle\Form\RecepcionCompraType;
use AppBundle\Form\RecepcionEditType;
use AppBundle\Entity\Insumo;
use AppBundle\Entity\Equipo;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockHistorico;
use AppBundle\Entity\EquipoUbicacion;

/**
 * @Route("/compra")
 */
class CompraController extends Controller {

    /**
     * @Route("/", name="compra_admin")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'compra');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
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
                    'cuenta' => $request->get('cuenta'),
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
                        'cuenta' => $sessionFiltro['cuenta'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('proveedorId' => 0, 'razonSocialId' => 0, 'solicitanteId' => 0, 'estado' => '',
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta'], 'cuenta' => '');
                }
                break;
        }
        $session->set('filtro_compras', $filtro);
        $proveedores = $em->getRepository('AppBundle:Proveedor')->findBy(array(), array('nombre' => 'ASC'));
        $razonSocial = $em->getRepository('ConfigBundle:Ubicacion')->findBy(array('razonSocial' => 1), array('nombre' => 'ASC'));
        if ($sessionFiltro['razonSocialId']) {
            $solicitantes = $em->getRepository('ConfigBundle:Ubicacion')->findDptosByUbicacionId($sessionFiltro['razonSocialId']);
        }
        else {
            $solicitantes = null;
        }

        //  $filtro = array('razonSocialId'=>$razonSocialId, 'solicitanteId'=>$solicitanteId ,'estado'=>$request->get('estado'),
        //      'proveedorId'=>$proveedorId, 'desde'=>$periodo['desde'], 'hasta'=>$periodo['hasta']);
        $entities = $em->getRepository('AppBundle:Compra')->findByCriteria($filtro);

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
     * @Template("AppBundle:Compra:edit.html.twig")
     */
    public function newAction() {
        $entity = new Compra();
        $entity->setFechaCompra(new \DateTime());
        $form = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @param Compra $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Compra $entity) {
        $form = $this->createForm(new CompraType(), $entity, array(
            'action' => $this->generateUrl('compra_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/", name="compra_create")
     * @Method("PUT")
     * @Template("AppBundle:Compra:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'compra_new');
        $data = $request->get('appbundle_compra');
        $em = $this->getDoctrine()->getManager();
        $new = $data['savenew'];
        $entity = new Compra();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $em->getConnection()->beginTransaction();
                if (isset($data['enviado'])) {
                    $entity->setEstado('ENVIADO');
                    $entity->setFechaEnvioProveedor(new \DateTime());
                }
                else {
                    $entity->setEstado('NUEVO');
                }
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();

                if ($new == 'S') {
                    $this->addFlash('success', 'La compra fue creada. Puede crear la siguiente!');
                    return $this->redirectToRoute('compra_admin_new');
                }
                else {
                    $this->addFlash('success', 'La compra fue creada!');
                    return $this->redirectToRoute('compra_admin');
                }
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', $ex->getMessage());
                // $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) );
            }
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="compra_admin_edit")
     * @Method("GET")
     * @Template("AppBundle:Compra:edit.html.twig")
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'compra_edit');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la compra.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * @param Compra $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Compra $entity) {
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
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la compra.');
        }
        $data = $request->get('appbundle_compra');
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                if (isset($data['enviado'])) {
                    $entity->setEstado('ENVIADO');
                    $entity->setFechaEnvioProveedor(new \DateTime());
                }
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirectToRoute('compra_admin');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', $ex->getMessage());
            }
        }
        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
        );
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
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
                    $entity->setEstado('ANULADO');
                    $entity->setUpdated(new \DateTime());
                    $em->persist($entity);
                    $em->flush();
                }
                $em->remove($entity);
                $em->flush();
                $em->getConnection()->commit();
                $this->addFlash('success', 'La compra fue anulada!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', UtilsController::errorMessage($ex->getMessage()));
            }
        }

        return $this->redirectToRoute('compra_admin');
    }

    /**
     * @Route("/{id}/show", name="compra_admin_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), 'compra');
        $em = $this->getDoctrine()->getManager();
        //$em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro de compra.');
        }
        return $this->render('AppBundle:Compra:show.html.twig', array(
                    'entity' => $entity,));
    }

    /**
     * @Route("/{id}/modalshow", name="modal_compra_admin_show")
     * @Method("GET")
     * @Template()
     */
    public function modalShowAction($id) {
        UtilsController::haveAccess($this->getUser(), 'compra');
        $em = $this->getDoctrine()->getManager();
        //$em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro de compra.');
        }
        $html = $this->renderView('AppBundle:Compra:modalshow.html.twig',
                array('entity' => $entity));
        return new Response($html);
    }

    /**
     * @Route("/{id}/recepcion", name="compra_admin_recepcion")
     * @Method("GET")
     * @Template()
     */
    public function recepcionAction($id) {
        UtilsController::haveAccess($this->getUser(), 'compra_recepcion');
        $em = $this->getDoctrine()->getManager();
        $compra = $em->getRepository('AppBundle:Compra')->find($id);
        if (!$compra) {
            throw $this->createNotFoundException('No se encuentra la compra.');
        }
        $recepcion = new RecepcionCompra();
        $recepcion->setFechaRecepcion(new \DateTime());

        /* foreach ($recepcion->getDetalles() as $key=>$det) {
          $form->get('detalles')[$key]->get('precio')->setData( $det->getCompraDetalle()->getPrecio() );
          } */
        // Armar registros para recepcion
        foreach ($compra->getDetalles() as $det) {
            if ($det->isPendiente()) {
                if ($det->getClaseDetalle() == 'E' && $det->getCantidadPendiente() > 1) {
                    // Si es + de un equipo corregir y dividir en la cantidad
                    $detrec = new RecepcionCompraDetalle();
                    $detrec->setCompraDetalle($det);
                    $detrec->setCantidad(1);
                    $detrec->setPrecio($det->getPrecio());
                    $detrec->setMoneda($det->getMoneda());
                    $recepcion->addDetalle($detrec);
                    $cant = $det->getCantidadPendiente() - 1;
                    for ($x = $cant; $x > 0; $x--) {
                        $detrec = new RecepcionCompraDetalle();
                        $detrec->setCompraDetalle($det);
                        $detrec->setCantidad(1);
                        $detrec->setPrecio($det->getPrecio());
                        $detrec->setMoneda($det->getMoneda());
                        $recepcion->addDetalle($detrec);
                    }
                }
                else {
                    $detrec = new RecepcionCompraDetalle();
                    $detrec->setCompraDetalle($det);
                    $detrec->setCantidad($det->getCantidadPendiente());
                    $detrec->setPrecio($det->getPrecio());
                    $detrec->setMoneda($det->getMoneda());
                    if ($det->getInsumo()) {
                        $detrec->setInsumo($det->getInsumo());
                    }
                    else {
                        // verificar que no exista un insumo con los mismos datos
                        $ins = $em->getRepository('AppBundle:Insumo')->findInsumoParaRecepcionCompra($det);
                        if ($ins) {
                            $detrec->setInsumo($ins);
                            $det->setNombre('');
                        }
                    }
                    $recepcion->addDetalle($detrec);
                }
            }
        }
        $compra->addRecepcion($recepcion);
        $form = $this->createForm(new RecepcionCompraType(), $recepcion, array(
            'action' => $this->generateUrl('compra_recepcion_create', array('id' => $compra->getId())),
            'method' => 'PUT',
        ));
        // Actualizar el formulario de recepcion.
        foreach ($recepcion->getDetalles() as $key => $recdet) {
            $detCompra = $recdet->getCompraDetalle();
            $form->get('detalles')[$key]->get('nombre')->setData($detCompra->getNombre());
            $form->get('detalles')[$key]->get('tipo')->setData($detCompra->getTipo());
            $form->get('detalles')[$key]->get('itemMarca')->setData($detCompra->getItemMarca());
            $form->get('detalles')[$key]->get('itemModelo')->setData($detCompra->getItemModelo());
        }
        return array(
            'entity' => $compra,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/{id}/recepcion", name="compra_recepcion_create")
     * @Method("PUT")
     * @Template("AppBundle:Compra:recepcion.html.twig")
     */
    public function createRecepcionAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'compra_recepcion');
        $em = $this->getDoctrine()->getManager();
        $recepcion = new RecepcionCompra();
        $form = $this->createForm(new RecepcionCompraType(), $recepcion, array(
            'action' => $this->generateUrl('compra_recepcion_create', array('id' => $id)),
            'method' => 'PUT',
        ));
        $recibir = false;
        $compra = $em->getRepository('AppBundle:Compra')->find($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $em->getConnection()->setAutoCommit(false);
                if (is_null($compra->getNroFactura()) && !is_null($recepcion->getNroFactura()))
                    $compra->setNroFactura($recepcion->getNroFactura());
                $formCompraDetalle = $request->get('appbundle_recepcioncompra')['detalles'];
                $deposito = $recepcion->getDeposito();
                foreach ($recepcion->getDetalles() as $key => $detalle) {
                    if ($detalle->getCantidad() > 0) {
                        $recibir = true;
                        $compdet = $detalle->getCompraDetalle();
                        $clase = $compdet->getClaseDetalle();
                        if ($detalle->getInsumo()) {
                            // Insumo existente
                            $producto = $em->getRepository('AppBundle:Insumo')->find($detalle->getInsumo()->getId());
                        }
                        else {
                            if ($clase == 'I') {
                                // insumo nuevo -> crear
                                $producto = new Insumo();
                                $producto->setBarcode($formCompraDetalle[$key]['nombre']);
                            }
                            else {
                                // equipo nuevo -> crear
                                $producto = new Equipo();
                                $producto->setVerificado(true);
                                $producto->setNroSerie($formCompraDetalle[$key]['nroSerie']);
                                $producto->setNombre($formCompraDetalle[$key]['nombre']);
                                $producto->setProveedor($compra->getProveedor());
                                $producto->setFechaCompra($compra->getFechaCompra());
                                $producto->setNroFactura($compra->getNroFactura());
                                $oc = ($compra->getRazonSocial()) ?
                                        $compra->getRazonSocial()->getAbreviatura() . '/' . $compra->getOrdenCompra() :
                                        $compra->getOrdenCompra();
                                $producto->setNroOrdenCompra($oc);
                                $producto->setNroRemito($recepcion->getNroRemito());
                                $estado = $em->getRepository('ConfigBundle:Estado')->findOneByInicial(1);
                                $producto->setEstado($estado);
                            }
                            $tipo = $em->getRepository('ConfigBundle:Tipo')->find($formCompraDetalle[$key]['tipo']);
                            $producto->setTipo($tipo);
                            $producto->setMarca($em->getRepository('ConfigBundle:Marca')->find($formCompraDetalle[$key]['itemMarca']));
                            $producto->setModelo($em->getRepository('ConfigBundle:Modelo')->find($formCompraDetalle[$key]['itemModelo']));
                            $em->persist($producto);
                            $em->flush();
                        }
                        if ($clase == 'I') {
                            $detalle->setInsumo($producto);
                            $compdet->setInsumo($producto);
                            // ajustar stock
                            $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($producto->getId(), $deposito->getId());
                            if (!$stock) {
                                $stock = new Stock();
                                $stock->setInsumo($producto);
                                $stock->setDeposito($deposito);
                                $stock->setCantidad(0);
                            }
                            $stock->setCantidad($stock->getCantidad() + $detalle->getCantidad());
                            $em->persist($stock);
                            $em->flush();
                            // Cargar movimiento
                            $movim = new StockHistorico();
                            $movim->setFecha($recepcion->getFechaRecepcion());
                            $movim->setTipo('COMPRA');
                            $movim->setSigno('+');
                            $movim->setMovimiento($compra->getId());
                            $movim->setInsumo($producto);
                            $movim->setStock($producto->getStockTotal());
                            $movim->setCantidad($detalle->getCantidad());
                            $movim->setDeposito($deposito);
                            $em->persist($movim);
                            $em->flush();
                        }
                        else {
                            $detalle->setEquipo($producto);
                            //$compdet->setEquipo($producto);
                            // formar barcode
                            $barcode = str_pad($producto->getTipo()->getId(), 3, '0', STR_PAD_LEFT) .
                                    str_pad($producto->getMarca()->getId(), 3, '0', STR_PAD_LEFT) .
                                    str_pad($producto->getModelo()->getId(), 3, '0', STR_PAD_LEFT) .
                                    str_pad($producto->getId(), 5, '0', STR_PAD_LEFT);
                            $producto->setBarcode($barcode);
                            // agregar ubicacion
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
                        $compdet->setRecibido($compdet->getRecibido() + $detalle->getCantidad());
                        //$compdet->setNroSerie( $formCompraDetalle[$key]['nroSerie'] );
                        $compdet->setPrecio($detalle->getPrecio());
                        $em->persist($compdet);
                        $em->flush();
                    }
                    else {
                        $recepcion->removeDetalle($detalle);
                    }
                }
                if ($recibir) {
                    $recepcion->setCompra($compra);
                    $estado = ( $compra->isCompleto() ) ? 'RECIBIDO' : 'RECEPCION PARCIAL';
                    $compra->setEstado($estado);
                    $em->persist($compra);
                    $em->persist($recepcion);
                    $em->flush();
                    //$this->recepcionDeCompra($em,$compra,$recepcion);
                    $em->getConnection()->commit();

                    $this->addFlash('success', 'Se ha registrado la recepción!');
                }
                else {
                    $em->getConnection()->rollback();
                    $this->addFlash('warning', 'No se registraron insumos ni equipos para la recepción!');
                }
                return $this->redirectToRoute('compra_admin');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $pos = strpos($ex->getMessage(), '1062');
                if ($pos !== false) {
                    $errormsj = 'Existe un registro duplicado!';
                }
                else {
                    $errormsj = $ex->getMessage();
                }
                $this->addFlash('danger', $errormsj);
                $compra->addRecepcion($recepcion);
            }
        }
        return array(
            'entity' => $compra,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/printCompra.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_compra")
     * @Method("GET")
     */
    public function printCompraAction($id) {
        $em = $this->getDoctrine()->getManager();
        //$em->getFilters()->disable('softdeleteable');
        $compra = $em->getRepository('AppBundle:Compra')->find($id);
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/homeTSG.png';
        $insumoIco = __DIR__ . '/../../../web/bundles/app/img/insumo_ico.jpg';
        $equipoIco = __DIR__ . '/../../../web/bundles/app/img/equipo_ico.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:Compra:compra.pdf.twig',
                array('compra' => $compra, 'logo' => $logo1, 'insumoIco' => $insumoIco, 'equipoIco' => $equipoIco), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=compra_' . $compra->getNroOc() . '.pdf'));
    }

    /**
     * @Route("/checkUniqueNombre", name="check_unique_nombre")
     * @Method("GET")
     */
    public function checkNombreEquipo(Request $request) {
        // verificar que el nombre sea unico en equipo
        $nombre = $request->get('txt');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $existe = $em->getRepository('AppBundle:Equipo')->findOneByNombre($nombre);
        if ($id && $existe) {
            if ($existe->getId() == $id)
                $existe = null;
        }
        return new Response($existe);
    }

    /**
     * @Route("/checkUniqueNroserie", name="check_unique_nroserie")
     * @Method("GET")
     */
    public function checkUniqueNroserie(Request $request) {
        // verificar que el nombre sea unico en equipo o insumo
        $nro = $request->get('txt');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $existe = $em->getRepository('AppBundle:Equipo')->findOneByNroSerie($nro);
        if ($id && $existe) {
            if ($existe->getId() == $id)
                $existe = null;
        }
        return new Response($existe);
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printListadoCompras", name="print_listado_compras")
     * @Method("POST")
     * @Template()
     */
    public function printListadoComprasAction(Request $request) {

        $op = $request->get('option');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $session = $this->get('session');
        $filtro = $session->get('filtro_compras');
        $searchTerm = $request->get('searchterm');
        $proveedor = $em->getRepository('AppBundle:Proveedor')->find($filtro['proveedorId']);
        $razonSocial = $em->getRepository('ConfigBundle:Ubicacion')->find($filtro['razonSocialId']);
        if ($filtro['razonSocialId']) {
            $solicitante = $em->getRepository('ConfigBundle:Ubicacion')->find($filtro['solicitanteId']);
        }
        else {
            $solicitante = null;
        }

        $arrayFiltro = array($razonSocial ? $razonSocial->getAbreviatura() : 'Todas', $solicitante ? $solicitante->getNombre() : 'Todos',
            $filtro['estado'] ? $filtro['estado'] : 'Todos', $proveedor ? $proveedor->getNombre() : 'Todos',
            $filtro['desde'], $filtro['hasta']);

        $hoy = new \DateTime();
        $entities = $em->getRepository('AppBundle:Compra')->findByCriteria($filtro, $searchTerm);
        switch ($op) {
            case 'pdf':
                $logo1 = __DIR__ . '/../../../web/bundles/app/img/homeTSG.png';
                $facade = $this->get('ps_pdf.facade');
                $response = new Response();
                $this->render('AppBundle:Compra:listado.pdf.twig', array('items' => $entities, 'filtro' => $arrayFiltro, 'logo' => $logo1,
                    'search' => $request->get('searchterm')), $response);

                $xml = $response->getContent();
                $content = $facade->render($xml);

                return new Response($content, 200, array('content-type' => 'application/pdf',
                    'Content-Disposition' => 'filename=listado_compras_' . $hoy->format('dmY_Hi') . '.pdf'));

            case 'xls':
                $entregas = 0;
                foreach ($entities as $compra) {
                    $entregas = (COUNT($compra->getRecepciones()) > $entregas) ? COUNT($compra->getRecepciones()) : $entregas;
                }
                /* return $this->render('AppBundle:Compra:listado-xls.html.twig',
                  array('items'=> $entities, 'filtro'=>$arrayFiltro,'search' => $searchTerm, 'entregas'=>$entregas)  ); */
                $partial = $this->renderView('AppBundle:Compra:listado-xls.html.twig',
                        array('items' => $entities, 'filtro' => $arrayFiltro, 'search' => $searchTerm, 'entregas' => $entregas));

                $fileName = 'Listado_Compras_' . $hoy->format('dmY_Hi');
                $response = new Response();
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
                $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
                $response->setContent($partial);
                return $response;
        }
    }

    /*
     * Editar recepción
     */

    /**
     * @Route("/{id}/renderEditarRecepcion", name="render_edit_recepcion_compra")
     * @Method("GET")
     */
    public function renderEditarRecepcion($id) {
        $em = $this->getDoctrine()->getManager();
        $recepcion = $em->getRepository('AppBundle:RecepcionCompra')->find($id);

        $form = $this->createForm(new RecepcionEditType(), $recepcion, array(
            'action' => $this->generateUrl('compra_recepcion_edit', array('id' => $id)),
            'method' => 'POST',
        ));

        $html = $this->renderView('AppBundle:Compra:partial-editar-recepcion.html.twig',
                array('entity' => $recepcion, 'form' => $form->createView()));
        return new Response($html);
    }

    /**
     * @Route("/{id}/recepcionEdit", name="compra_recepcion_edit")
     * @Method("POST")
     */
    public function recepcionEdit(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $recepcion = $em->getRepository('AppBundle:RecepcionCompra')->find($id);

        // clonar los datos para controlar las modificaciones
        $cabAnterior = clone $recepcion;
        $detAnterior = new ArrayCollection();
        foreach ($recepcion->getDetalles() as $item) {
            $obj = clone $item;
            $detAnterior->add($obj);
        }

        $form = $this->createForm(new RecepcionEditType(), $recepcion, array(
            'action' => $this->generateUrl('compra_recepcion_edit', array('id' => $id)),
            'method' => 'POST',
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();
            $em->getConnection()->setAutoCommit(false);
            try {
                // datos de compra
                $compra = $em->getRepository('AppBundle:Compra')->find($recepcion->getCompra()->getId());
                $compra->setNroFactura($request->get('appbundle_recepcioncompra')['compra']['nroFactura']);
                $compra->setDescripcion($request->get('appbundle_recepcioncompra')['compra']['descripcion']);
                $compra->setFechaCompra(new \DateTime(UtilsController::toAnsiDate($request->get('appbundle_recepcioncompra')['compra']['fechaCompra']) . ' ' . '00:00'));
                $em->persist($compra);
                $em->flush();
                // procesar los datos si es insumo
                foreach ($recepcion->getDetalles() as $key => $item) {
                    if ($item->getInsumo()) {
                        if ($item->getCantidad() != $detAnterior[$key]->getCantidad()) {
                            $producto = $item->getInsumo();
                            $deposito = $recepcion->getDeposito();
                            $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($producto, $deposito);
                            if (!$stock) {
                                throw new \Exception('No se pudo realizar la operación!');
                            }
                            // calcular diferencia en el ingreso
                            $cantidad = $detAnterior[$key]->getCantidad() - $item->getCantidad();
                            $stock->setCantidad($stock->getCantidad() - $cantidad);
                            $em->persist($stock);
                            $em->flush();
                            // Cargar movimiento historico
                            $signo = ( $detAnterior[$key]->getCantidad() > $item->getCantidad() ) ? '-' : '+';
                            $movim = new StockHistorico();
                            $movim->setFecha(new \DateTime());
                            $movim->setTipo('COMPRA');
                            $movim->setSigno($signo);
                            $movim->setMovimiento($recepcion->getCompra()->getId());
                            $movim->setInsumo($item->getInsumo());
                            $movim->setStock($item->getInsumo()->getStockTotal());
                            $movim->setCantidad(abs($cantidad));
                            $movim->setDeposito($recepcion->getDeposito());
                            $em->persist($movim);
                            $em->flush();
                        }
                    }
                }
                // archivo
                /* $file = $request->files->get('appbundle_recepcioncompra')['file'];
                  if ($cabAnterior->getPath() == NULL && $file) {
                  $recepcion->setFile($file);
                  } */
                $em->persist($recepcion);
                $em->flush();
                $em->getConnection()->commit();

                $this->addFlash('success', 'Los datos se modificaron!');
                return new Response('OK');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                return new Response($ex->getMessage());
            }
        }



        $errors = array();

        // Global
        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        // Fields
        foreach ($form as $child /** @var Form $child */) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }


        return new Response(json_encode($errors));
    }

    /**
     * @Route("/{id}/recepcionDelete", name="compra_recepcion_delete")
     * @Method("POST")
     */
    public function recepcionDelete($id) {
        $em = $this->getDoctrine()->getManager();
        $recepcion = $em->getRepository('AppBundle:RecepcionCompra')->find($id);
        $em->getConnection()->beginTransaction();
        $em->getConnection()->setAutoCommit(false);
        $msjequipo = '';

        try {
            foreach ($recepcion->getDetalles() as $item) {
                if ($item->getInsumo()) {
                    // INSUMO
                    // anular carga de stock o equipo antes de eliminar
                    $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($item->getInsumo(), $recepcion->getDeposito()->getId());
                    if (!$stock) {
                        throw new \Exception('No se pudo realizar la operación!');
                    }
                    $stock->setCantidad($stock->getCantidad() - $item->getCantidad());
                    $em->persist($stock);
                    $em->flush();
                    // Cargar movimiento historico
                    $movim = new StockHistorico();
                    $movim->setFecha($recepcion->getFechaRecepcion());
                    $movim->setTipo('COMPRA');
                    $movim->setSigno('-');
                    $movim->setMovimiento($recepcion->getCompra()->getId());
                    $movim->setInsumo($item->getInsumo());
                    $movim->setStock($item->getInsumo()->getStockTotal());
                    $movim->setCantidad($item->getCantidad());
                    $movim->setDeposito($recepcion->getDeposito());
                    $em->persist($movim);
                    $em->flush();
                }
                else {
                    // EQUIPO
                    // eliminar el equipo
                    $equipo = $em->getRepository('AppBundle:Equipo')->find($item->getEquipo()->getId());
                    if (!$equipo) {
                        throw $this->createNotFoundException('No existe el equipo.');
                    }
                    /* if (is_null($equipo->getDeletedAt())) {
                      //forzar el guardado de ultima fecha de modificaciÃ³n antes de softdelete
                      $em->getFilters()->enable('softdeleteable');
                      $equipo->setUpdated(new \DateTime());
                      $em->persist($equipo);
                      $em->flush();
                      } */
                    $em->remove($equipo);
                    $em->flush();
                    $msjequipo = '. También se han eliminado los equipos asociados.';
                    // blanquear datos en compra_detalle
                    $item->getCompraDetalle()->setEquipo(null);
                    $item->getCompraDetalle()->setNroSerie(null);
                }
                // modificar registro compra_detalle
                $dif = $item->getCompraDetalle()->getRecibido() - $item->getCantidad();
                $item->getCompraDetalle()->setRecibido($dif);
            }


            $em->remove($recepcion);
            $em->flush();
            // Corregir el estado de la compra
            $compra = $recepcion->getCompra();
            $estado = ( $compra->isCompleto() ) ? 'RECIBIDO' : 'RECEPCION PARCIAL';
            $compra->setEstado($estado);
            $em->persist($compra);
            $em->flush();
            $em->getConnection()->commit();
            $this->addFlash('success', 'La entrega fue eliminada!' . $msjequipo);
            return new Response('OK');
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            return new Response($ex->getMessage());
        }
        return new Response('ERROR');
    }

    /**
     * PLANILLA PARA ADMINISTRACION DE ESTADO DE EQUIPOS
     */

    /**
     * @Route("/bienesEnStock", name="compra_bienes_stock")
     * @Method("GET")
     */
    public function bienesEnStockAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'compra');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_compras_bienes');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'razonSocialId' => $request->get('razonSocialId'),
                    'proveedorId' => $request->get('proveedorId'),
                    'desde' => $periodo['desde'],
                    'hasta' => $periodo['hasta'],
                    'movDesde' => $request->get('movDesde'),
                    'movHasta' => $request->get('movHasta'),
                    'estado' => $request->get('estado'),
                );
                break;
            case 'limpiar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'razonSocialId' => 0,
                    'proveedorId' => 0,
                    'desde' => $periodo['desde'],
                    'hasta' => $periodo['hasta'],
                    'movDesde' => '',
                    'movHasta' => '',
                    'estado' => '0',
                );
                break;
            default:
                //desde paginacion, se usa session
                if (!$sessionFiltro) {
                    $periodo = UtilsController::ultimoMesParaFiltro($sessionFiltro['desde'], $sessionFiltro['hasta']);
                    $filtro = array(
                        'razonSocialId' => $sessionFiltro['razonSocialId'],
                        'proveedorId' => $sessionFiltro['proveedorId'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                        'movDesde' => $sessionFiltro['movDesde'],
                        'movHasta' => $sessionFiltro['movHasta'],
                        'estado' => $sessionFiltro['estado'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('proveedorId' => 0, 'razonSocialId' => 0, 'estado' => '0',
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta'], 'movDesde' => '', 'movHasta' => '');
                }
                break;
        }
        $session->set('filtro_compras_bienes', $filtro);
        $proveedores = $em->getRepository('AppBundle:Proveedor')->findBy(array(), array('nombre' => 'ASC'));
        $razonesSociales = $em->getRepository('ConfigBundle:Ubicacion')->findBy(array('razonSocial' => '1'), array('abreviatura' => 'ASC'));


        $datos = $this->getBienesEnStock($filtro, $em);

        return $this->render('AppBundle:Compra:bienes-en-stock.html.twig', array(
                    'datos' => $datos,
                    'proveedores' => $proveedores,
                    'razonesSociales' => $razonesSociales,
                    'filtro' => $filtro
        ));
    }

    /**
     * @Route("/printBienesEnStock", name="print_bienes_en_stock")
     * @Method("POST")
     * @Template()
     */
    public function printBienesEnStockAction(Request $request) {

        $op = $request->get('option');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $filtro = $session->get('filtro_compras_bienes');
        //$searchTerm = $request->get('searchterm');
        $proveedor = $em->getRepository('AppBundle:Proveedor')->find($filtro['proveedorId']);
        $razonSocial = $em->getRepository('ConfigBundle:Ubicacion')->find($filtro['razonSocialId']);
        $estado = $em->getRepository('ConfigBundle:Estado')->find($filtro['estado']);
        $arrayFiltro = array(
            $razonSocial ? $razonSocial->getAbreviatura() : 'Todas',
            $proveedor ? $proveedor->getNombre() : 'Todos',
            $estado ? $estado->getNombre() : 'Todos',
            $filtro['desde'], $filtro['hasta'],
            $filtro['movDesde'], $filtro['movHasta']
        );
        $hoy = new \DateTime();
        $datos = $this->getBienesEnStock($filtro, $em);
        switch ($op) {
            case 'pdf':
                $logo1 = __DIR__ . '/../../../web/bundles/app/img/homeTSG.png';
                $facade = $this->get('ps_pdf.facade');
                $response = new Response();
                $this->render('AppBundle:Compra:listado.pdf.twig', array('items' => $datos, 'filtro' => $arrayFiltro, 'logo' => $logo1,
                    'search' => $request->get('searchterm')), $response);

                $xml = $response->getContent();
                $content = $facade->render($xml);

                return new Response($content, 200, array('content-type' => 'application/pdf',
                    'Content-Disposition' => 'filename=listado_compras_' . $hoy->format('dmY_Hi') . '.pdf'));

            case 'xls':
                $partial = $this->renderView('AppBundle:Compra:bienes-en-stock-xls.html.twig',
                        array('items' => $datos, 'filtro' => $arrayFiltro));

                $fileName = 'Bienes_en_Stock_' . $hoy->format('dmY_Hi');
                $response = new Response();
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
                $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
                $response->setContent($partial);
                return $response;
        }
    }

    private function getBienesEnStock($filtro, $em) {
        $entities = $em->getRepository('AppBundle:Compra')->getBienesEnStock($filtro);
        $desde = ($filtro['movDesde']) ? date('Ymd', strtotime($filtro['movDesde'])) : '';
        $hasta = ($filtro['movHasta']) ? date('Ymd', strtotime($filtro['movHasta'])) : '';
        $datos = array();
        foreach ($entities as $entity) {
            $ubicInicial = $em->getRepository('AppBundle:Equipo')->getPrimeraUbicacionParaBienes($entity->getId());
            // TRAER LA OT PARA TENER TODOS LOS DATOS DE PUESTO EN OPERATIVO.
            $tareaop = $em->getRepository('AppBundle:Equipo')->getTareaOperativoParaBienes($entity->getId());
            $fechaMovimiento = $idOT = $nroOT = $nuevaUbicacion = '';
            if ($desde && $hasta) {
                if (count($tareaop) == 0) {
                    // saltear equipo por no haber sido instalado
                    continue;
                }
                else {
                    // verificar la fecha de instalacion con el rango
                    $fecha = $tareaop->getFecha()->format('Ymd');
                    if (!($fecha >= $desde && $fecha <= $hasta)) {
                        continue;
                    }
                }
            }
            if (count($tareaop) > 0) {
                $fechaMovimiento = $tareaop->getFecha()->format('d/m/Y');
                $idOT = $tareaop->getOrdenTrabajo()->getId();
                $nroOT = $tareaop->getOrdenTrabajo()->getNroOT();
                if ($tareaop->getOrdenTrabajoDetalles()[0]->getEquipoUbicacionFinal()) {
                    $nuevaUbicacion = '';
                    foreach ($tareaop->getOrdenTrabajoDetalles() as $tarea) {
                        $nuevaUbicacion = $nuevaUbicacion . 'f ' . $tarea->getEquipoUbicacionFinal()->getTexto();
                    }
                }
                else {
                    $nuevaUbicacion = 'Verificar: OTD' . $tareaop->getOrdenTrabajoDetalles()[0]->getId() . ' EQ' . $entity->getId();
                }
            }

            $datos[] = array(
                'razonSocial' => $entity->getRazonSocial(),
                'proveedor' => $entity->getProveedor(),
                'nombre' => $entity->getNombre(),
                'nroSerie' => $entity->getNroSerie(),
                'ubicInicial' => ($ubicInicial) ? $ubicInicial->getDepartamento() : '',
                'cuenta' => $entity->getCuenta(),
                'factura' => $entity->getFactura(),
                'remito' => $entity->getRemito(),
                'nroOC' => $entity->getOrdenCompra()['id'],
                'txtOC' => $entity->getOrdenCompra()['txt'],
                'fechaAdquisicion' => $entity->getFechaAdquisicion(),
                'precioDolares' => $entity->getPrecioDolares(),
                'cotizacionEquipo' => $entity->getCotizacionEquipo(),
                'precioPesos' => $entity->getPrecioPesos(),
                'fechaMovimiento' => $fechaMovimiento,
                'idOT' => $idOT,
                'nroOT' => $nroOT,
                'nuevaUbicacion' => $nuevaUbicacion,
                'estado' => $entity->getEstado()
            );
        }
        return $datos;
    }

}