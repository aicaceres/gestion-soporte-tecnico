<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\Requerimiento;
use AppBundle\Form\RequerimientoNewType;
use AppBundle\Form\RequerimientoType;
use AppBundle\Entity\RequerimientoDetalle;
use AppBundle\Form\RequerimientoDetalleType;
use AppBundle\Entity\EquipoUbicacion;
use AppBundle\Entity\OrdenTrabajo;
use AppBundle\Entity\OrdenTrabajoDetalle;
use AppBundle\Form\OrdenTrabajoDetalleType;
use AppBundle\Entity\Mensajeria;

/**
 * @Route("/soporte_requerimiento")
 */
class RequerimientoController extends Controller {

    /**
     * @Route("/", name="soporte_requerimiento")
     * @Method("GET")
     * @Template("AppBundle:Requerimiento:index.html.twig")
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'requerimiento');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_requerimiento');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'idUbicacion' => $request->get('idUbicacion'),
                    'idEdificio' => $request->get('idEdificio'),
                    'idDepartamento' => $request->get('idDepartamento'),
                    'idTipo' => $request->get('idTipo'),
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
                        'idUbicacion' => $sessionFiltro['idUbicacion'],
                        'idEdificio' => $sessionFiltro['idEdificio'],
                        'idDepartamento' => $sessionFiltro['idDepartamento'],
                        'idTipo' => $sessionFiltro['idTipo'],
                        'estado' => $sessionFiltro['estado'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('idDepartamento' => 0, 'idEdificio' => 0, 'idUbicacion' => 0, 'estado' => 'SIN ASIGNAR',
                        'idTipo' => 0, 'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_requerimiento', $filtro);
        $userId = $this->getUser()->getId();
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);
        $edificios = null;
        $departamentos = null;
        if ($filtro['idUbicacion']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findByUbicacion($filtro['idUbicacion']);
            if ($filtro['idEdificio']) {
                $departamentos = $em->getRepository('ConfigBundle:Departamento')->findByEdificio($filtro['idEdificio']);
            }
        }
        $tipoSoporte = $em->getRepository('ConfigBundle:TipoSoporte')->findAll();
        $entities = $em->getRepository('AppBundle:Requerimiento')->findByCriteria($filtro, $userId);

        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }
        return array(
            'entities' => $entities,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'tipoSoporte' => $tipoSoporte,
            'filtro' => $filtro,
            'deleteForms' => $deleteForms
        );
    }

    /**
     * @Route("/new", name="soporte_requerimiento_new")
     * @Method("GET")
     * @Template("AppBundle:Requerimiento:new.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), 'requerimiento_new');
        $em = $this->getDoctrine()->getManager();
        $entity = new Requerimiento();
        $entity->setFechaRequerimiento(new \DateTime());
        $entity->setEstado('NUEVO');
        $servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
        if ($servTecnico) {
            $entity->setDepartamentoEquipo($servTecnico);
            $entity->setPisoEquipo($servTecnico->getPisos()[0]);
        }
        $form = $this->createNewForm($entity);
        $form->get('hora')->setData($entity->getFechaRequerimiento()->format('H:i'));
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($this->getUser()->getId());
        return array(
            'entity' => $entity,
            'ubicaciones' => $ubicaciones,
            'servTecnico' => $servTecnico,
            'form' => $form->createView(),
        );
    }

    /**
     * @param Requerimiento $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createNewForm(Requerimiento $entity) {
        $form = $this->createForm(new RequerimientoNewType(), $entity, array(
            'action' => $this->generateUrl('soporte_requerimiento_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/", name="soporte_requerimiento_create")
     * @Method("PUT")
     * @Template("AppBundle:Requerimiento:new.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'requerimiento_new');
        $data = $request->get('appbundle_requerimiento');
        $ckEstado = $request->get('ckEstadoEquipo');
        $ckUbicacion = $request->get('ckubicacionEquipo');
        $entity = new Requerimiento();
        $form = $this->createNewForm($entity);
        $form->handleRequest($request);
        $ot = null;
        $msj = '';
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $em->getConnection()->setAutoCommit(false);
                $entity->setEstado('SIN ASIGNAR');
                $entity->setFechaRequerimiento(new \DateTime(UtilsController::toAnsiDate($data['fechaRequerimiento']) . ' ' . $data['hora'] . ':00'));
                if (count($entity->getDetalles()) > 0) {
                    foreach ($entity->getDetalles() as $detalle) {
                        $equipo = $detalle->getEquipo();
                        $detalle->setEstadoOriginal($equipo->getEstado());
                        if ($ckEstado) {
                            $equipo->setEstado($entity->getEstadoEquipo());
                        }
                        $ubicOrignal = $equipo->getUbicacionActual();
                        $detalle->setEquipoUbicacionOriginal($ubicOrignal);
                        if ($ckUbicacion) {
                            $dpto = $entity->getDepartamentoEquipo();
                            $piso = $entity->getPisoEquipo();
                            // quitar equipo actual para agregar el nuevo
                            self::unsetUbicaciones($em, $equipo->getId());
                            //
                            $ubicacion = new EquipoUbicacion();
                            $ubicacion->setActual(true);
                            $ubicacion->setDepartamento($dpto);
                            $ubicacion->setEdificio($dpto->getEdificio());
                            $ubicacion->setUbicacion($dpto->getEdificio()->getUbicacion());
                            $ubicacion->setPiso($piso);
                            $ubicacion->setFechaEntrega(new \DateTime());
                            $ubicacion->setConceptoEntrega($em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByAbreviatura('ST'));
                            $ubicacion->setRequerimientoDetalle($detalle);
                            $detalle->setEquipoUbicacionRequerimiento($ubicacion);
                            $equipo->addUbicacion($ubicacion);
                        }
                        $em->persist($equipo);
                        $em->flush();
                    }
                }
                $em->persist($entity);
                $em->flush();
                $tecnico = $em->getRepository('ConfigBundle:Usuario')->find($data['tecnico']);
                if ($tecnico) {
                    $ot = $this->asignarRequerimiento($entity, $tecnico, $em);
                    $entity->setEstado('ASIGNADO');
                    $msj = 'Se ha creado la OT N° ' . $ot->getNroOT();
                    $em->persist($entity);
                    $em->flush();
                    $session = $this->get('session');
                    $session->set('otid', $ot->getId());
                }
                $em->getConnection()->commit();
                $this->addFlash('success', 'El requerimiento fue creado correctamente. ' . $msj);
                return $this->redirectToRoute('soporte_requerimiento_edit', array('id' => $entity->getId()));
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', $ex->getMessage());
                $em->getConnection()->rollback();
                //$this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($this->getUser()->getId());
        $servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
        return array(
            'entity' => $entity,
            'ubicaciones' => $ubicaciones,
            'servTecnico' => $servTecnico,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="soporte_requerimiento_edit")
     * @Method("GET")
     * @Template("AppBundle:Requerimiento:edit.html.twig")
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'requerimiento_edit');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Requerimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el requerimiento.');
        }
        $editForm = $this->createEditForm($entity);
        $editForm->get('hora')->setData($entity->getFechaRequerimiento()->format('H:i'));
        $deleteForm = $this->createDeleteForm($id);
        foreach ($entity->getOrdentrabajoAsociadas() as $key => $ot) {
            $editForm->get('ordentrabajoAsociadas')[$key]->get('hora')->setData($ot->getFechaOrden()->format('H:i'));
        }
        $servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
        if ($servTecnico) {
            $entity->setDepartamentoEquipo($servTecnico);
            $entity->setPisoEquipo($servTecnico->getPisos()[0]);
        }
        //$repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
        //$item = $em->find('AppBundle\Entity\Insumo', $id /*article id*/);
        //$logs = $repo->getLogEntries($item);

        return array(
            'requerimiento' => $entity,
            'servTecnico' => $servTecnico,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * @param Requerimiento $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Requerimiento $entity) {
        $form = $this->createForm(new RequerimientoType(), $entity, array(
            'action' => $this->generateUrl('sopote_requerimiento_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sopote_requerimiento_update")
     * @Method("PUT")
     * @Template("AppBundle:Requerimiento:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'requerimiento_edit');
        $data = $request->get('appbundle_requerimiento');
        $ckEstado = $request->get('ckEstadoEquipo');
        $ckUbicacion = $request->get('ckubicacionEquipo');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Requerimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Requerimiento.');
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        $ot = null;
        $msj = '';
        $session = $this->get('session');
        $session->set('otid', $data['otid']);
        if ($editForm->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity->setFechaRequerimiento(new \DateTime(UtilsController::toAnsiDate($data['fechaRequerimiento']) . ' ' . $data['hora'] . ':00'));
                /* if (count($entity->getDetalles()) > 0) {
                  foreach ($entity->getDetalles() as $detalle) {
                  if (!$detalle->getId()) {
                  $equipo = $detalle->getEquipo();
                  if ($ckEstado) {
                  $detalle->setEstadoOriginal($equipo->getEstado());
                  $equipo->setEstado($entity->getEstadoEquipo());
                  }
                  $ubicOrignal = $equipo->getUbicacionActual();
                  if ($ckUbicacion) {
                  $dpto = $entity->getDepartamentoEquipo();
                  $piso = $entity->getPisoEquipo();
                  $ubicacion = new EquipoUbicacion();
                  $ubicacion->setActual(true);
                  $ubicacion->setDepartamento($dpto);
                  $ubicacion->setEdificio($dpto->getEdificio());
                  $ubicacion->setUbicacion($dpto->getEdificio()->getUbicacion());
                  $ubicacion->setPiso($piso);
                  $ubicacion->setFechaEntrega(new \DateTime());
                  $detalle->setEquipoUbicacionOriginal($ubicOrignal);
                  $ubicOrignal->setActual(0);
                  $equipo->addUbicacion($ubicacion);
                  }
                  $em->persist($equipo);
                  $em->flush();
                  }
                  }
                  } */

                foreach ($entity->getOrdentrabajoAsociadas() as $key => $ot) {
                    $dataot = $data['ordentrabajoAsociadas'][$key];
                    $ot->setFechaOrden(new \DateTime(UtilsController::toAnsiDate($dataot['fechaOrden']) . ' ' . $dataot['hora'] . ':00'));
                }
                if ($entity->getEstado() == 'SIN ASIGNAR' && $data['tecnico']) {
                    $tecnico = $em->getRepository('ConfigBundle:Usuario')->find($data['tecnico']);
                    $ot = $this->asignarRequerimiento($entity, $tecnico, $em);
                    if ($ot) {
                        $session->set('otid', $ot->getId());
                        $msj = 'Se ha creado la OT N° ' . $ot->getNroOT();
                        $entity->setEstado('ASIGNADO');
                        $em->persist($entity);
                        $em->flush();
                    }
                    else {
                        $msj = 'No se ha podido generar la OT para asignar el requerimiento.';
                    }
                }
                $em->flush();
                $em->getConnection()->commit();
                if ($data['otid']) {
                    $otnro = $em->getRepository('AppBundle:OrdenTrabajo')->find($data['otid']);
                    $this->addFlash('success', 'La OT N°' . $otnro->getNroOT() . ' fue guardada correctamente.');
                }
                else {
                    $this->addFlash('success', 'El requerimiento fue guardado correctamente. ' . $msj);
                }
                if ($this->getUser()->getRol()->getAdmin()) {
                    $em->getFilters()->enable('softdeleteable');
                }
                return $this->redirectToRoute('soporte_requerimiento_edit', array('id' => $entity->getId()));
                //return $this->forward('AppBundle:Requerimiento:edit', array('id'  => $entity->getId()));
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                //$this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) );
                $this->addFlash('danger', $ex->getMessage());
            }
        }

        $deleteForm = $this->createDeleteForm($id);
        return array(
            'delete_form' => $deleteForm->createView(),
            'requerimiento' => $entity,
            'form' => $editForm->createView()
        );
    }

    /**
     * @Route("/{id}/show", name="soporte_requerimiento_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), 'requerimiento');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Requerimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el requerimiento.');
        }
        $html = $this->renderView('AppBundle:Requerimiento:show.html.twig',
                array('entity' => $entity));
        return new Response($html);
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('soporte_requerimiento_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm();
    }

    /**
     * @Route("/delete/{id}", name="soporte_requerimiento_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'requerimiento_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity = $em->getRepository('AppBundle:Requerimiento')->find($id);
                if (!$entity) {
                    throw $this->createNotFoundException('No existe el requerimiento.');
                }
                if (is_null($entity->getDeletedAt())) {
                    //forzar el guardado de ultima fecha de modificación antes de softdelete
                    $em->getFilters()->enable('softdeleteable');
                    $entity->setUpdated(new \DateTime());
                    // marcar como cancelado
                    $entity->setEstado('CANCELADO');
                    $em->persist($entity);
                    $em->flush();
                    // volver estado y ubicación original los equipos
                    foreach ($entity->getDetalles() as $detalle) {
                        $equipo = $detalle->getEquipo();
                        if ($detalle->getEstadoOriginal()) {
                            $equipo->setEstado($detalle->getEstadoOriginal());
                        }
                        if ($detalle->getEquipoUbicacionOriginal()) {
                            $origen = $detalle->getEquipoUbicacionOriginal();
                            // quitar equipo actual para agregar el nuevo
                            self::unsetUbicaciones($em, $equipo->getId());
                            //
                            $ubicacion = new EquipoUbicacion();
                            $ubicacion->setActual(true);
                            $ubicacion->setDepartamento($origen->getDepartamento());
                            $ubicacion->setEdificio($origen->getDepartamento()->getEdificio());
                            $ubicacion->setUbicacion($origen->getDepartamento()->getEdificio()->getUbicacion());
                            $ubicacion->setPiso($origen->getPiso());
                            $ubicacion->setFechaEntrega(new \DateTime());
                            $equipo->addUbicacion($ubicacion);
                        }
                        $em->persist($equipo);
                        if ($detalle->getOrdenTrabajoDetalle()) {
                            $detalle->getOrdenTrabajoDetalle()->setEntregado(1);
                            $em->persist($detalle);
                        }
                        $em->flush();
                    }
                    // cancelar las ot
                    if (count($entity->getOrdentrabajoAsociadas()) > 0) {
                        foreach ($entity->getOrdentrabajoAsociadas() as $ot) {
                            // LIBERAR EQUIPOS
                            foreach ($ot->getDetalles() as $otDet) {
                                if (!$otDet->getEntregado()) {
                                    // reubicar equipo
                                    $equipo = $otDet->getEquipo();
                                    if ($otDet->getEstadoOriginal()) {
                                        $equipo->setEstado($otDet->getEstadoOriginal());
                                    }
                                    if ($otDet->getEquipoUbicacionOriginal()) {
                                        $origen = $otDet->getEquipoUbicacionOriginal();
                                        // quitar equipo actual para agregar el nuevo
                                        self::unsetUbicaciones($em, $equipo->getId());
                                        //
                                        $ubicacion = new EquipoUbicacion();
                                        $ubicacion->setActual(true);
                                        $ubicacion->setDepartamento($origen->getDepartamento());
                                        $ubicacion->setEdificio($origen->getDepartamento()->getEdificio());
                                        $ubicacion->setUbicacion($origen->getDepartamento()->getEdificio()->getUbicacion());
                                        $ubicacion->setPiso($origen->getPiso());
                                        $ubicacion->setFechaEntrega(new \DateTime());
                                        $equipo->addUbicacion($ubicacion);
                                    }
                                    $em->persist($equipo);
                                    $otDet->setEntregado(1);
                                    $em->persist($otDet);
                                }
                            }
                            $ot->setEstado('CANCELADO');
                            $em->persist($ot);
                            $em->flush();
                            $em->remove($ot);
                            $em->flush();
                        }
                    }
                }
                $em->remove($entity);
                $em->flush();
                $em->getConnection()->commit();
                $this->addFlash('success', 'El requerimiento fue cancelado!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return $this->redirectToRoute('soporte_requerimiento');
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printListadoRequerimientos", name="print_listado_requerimientos")
     * @Method("POST")
     * @Template()
     */
    public function printListadoRequerimientosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $session = $this->get('session');
        $filtro = $session->get('filtro_requerimiento');
        $tipo = $em->getRepository('ConfigBundle:TipoSoporte')->find($filtro['idTipo']);
        $ubicacion = $em->getRepository('ConfigBundle:Ubicacion')->find($filtro['idUbicacion']);
        $edificio = $em->getRepository('ConfigBundle:Edificio')->find(($filtro['idEdificio']) ? $filtro['idEdificio'] : 0);
        $departamento = $em->getRepository('ConfigBundle:Departamento')->find(($filtro['idDepartamento']) ? $filtro['idDepartamento'] : 0);
        $periodo = UtilsController::ultimoMesParaFiltro($filtro['estado'], $filtro['estado']);
        $arrayFiltro = array($tipo ? $tipo->getNombre() : 'Todos', $filtro['estado'] ? $filtro['estado'] : 'Todos',
            $ubicacion ? $ubicacion->getAbreviatura() : 'Todos', $edificio ? $edificio->getNombre() : 'Todos',
            $departamento ? $departamento->getNombre() : 'Todos', $periodo['desde'], $periodo['hasta']);

        $hoy = new \DateTime();
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/pdf_logo.png';
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:Requerimiento:listado.pdf.twig', array('items' => json_decode($items), 'filtro' => $arrayFiltro,
            'logo' => $logo1, 'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);

        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_requerimientos_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("{id}/pdfRequerimiento.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_requerimiento")
     * @Method("GET")
     */
    public function pdfRequerimientoAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $req = $em->getRepository('AppBundle:Requerimiento')->find($id);
        $reqNro = str_pad($req->getId(), 6, '0', STR_PAD_LEFT);
        $fecha = UtilsController::longDateSpanish($req->getFechaRequerimiento());
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:Requerimiento:requerimiento.pdf.twig',
                array('req' => $req, 'logo' => $logo1, 'fecha' => $fecha), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=informe_requerimiento_' . $reqNro . '.pdf'));
    }

    /**
     * @Route("{id}/pdfRequerimientoRecepcionEquipo.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_requerimiento_recepcion_equipo")
     * @Method("GET")
     */
    public function pdfRequerimientoRecepcionEquipoAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $req = $em->getRepository('AppBundle:Requerimiento')->find($id);
        $reqNro = str_pad($req->getId(), 6, '0', STR_PAD_LEFT);
        $fecha = UtilsController::longDateSpanish($req->getFechaRequerimiento());
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:Requerimiento:requerimiento_recepcion_equipo.pdf.twig',
                array('req' => $req, 'logo' => $logo1, 'fecha' => $fecha), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=acta_recepcion_requerimiento_' . $reqNro . '.pdf'));
    }

    /**
     * @Route("{id}/pdfRequerimientoActaFinalizacion.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_requerimiento_acta_finalizacion")
     * @Method("GET")
     */
    public function pdfRequerimientoActaFinalizacionAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $req = $em->getRepository('AppBundle:Requerimiento')->find($id);
        $reqNro = str_pad($req->getId(), 6, '0', STR_PAD_LEFT);
        $fecha = UtilsController::longDateSpanish($req->getFechaRequerimiento());
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:Requerimiento:requerimiento_acta_finalizacion.pdf.twig',
                array('req' => $req, 'logo' => $logo1, 'fecha' => $fecha), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=acta_finalizacion_requerimiento_' . $reqNro . '.pdf'));
    }

    /*     * *
     * FUNCIONES AJAX
     */

    /**
     * @Route("/ajax/changeEstadoRequerimiento", name="change_estado_requerimiento")
     * @Method("POST")
     */
    public function changeEstadoRequerimiento(Request $request) {
        $em = $this->getDoctrine()->getManager();
        try {
            $req = $em->getRepository('AppBundle:Requerimiento')->find($request->get('id'));
            $req->setEstado($request->get('estado'));
            $em->persist($req);
            $em->flush();
            return new Response('OK');
        }
        catch (\Exception $ex) {
            return new Response('ERROR');
        }
    }

    /**
     * @Route("/renderAsignarRequerimiento", name="render_asignar_requerimiento")
     * @Method("GET")
     */
    public function renderAsignarRequerimiento(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $requerimiento = $em->getRepository('AppBundle:Requerimiento')->find($request->get('id'));
        $tecnicos = $em->getRepository('ConfigBundle:Usuario')->findTecnicosHabilitados($requerimiento->getSolicitante()->getEdificio()->getId());
        $html = $this->renderView('AppBundle:Requerimiento:partial-asignar-tecnico.html.twig',
                array('requerimiento' => $requerimiento, 'tecnicos' => $tecnicos));
        return new Response($html);
    }

    /**
     * @Route("/ajaxAsignarRequerimiento", name="ajax_asignar_requerimiento")
     * @Method("POST")
     */
    public function ajaxAsignarRequerimiento(Request $request) {
        $reqId = $request->get('req');
        $tecId = $request->get('tec');
        $reqform = $request->get('reqform');
        $em = $this->getDoctrine()->getManager();
        $tecnico = $em->getRepository('ConfigBundle:Usuario')->find($tecId);
        $req = $em->getRepository('AppBundle:Requerimiento')->find($reqId);
        $ot = $this->asignarRequerimiento($req, $tecnico, $em);
        if ($ot) {
            $req->setEstado('ASIGNADO');
            $em->persist($req);
            $em->flush();
            if ($reqform) {
                $session = $this->get('session');
                $session->set('otid', $ot->getId());
            }
            $this->addFlash('success', 'Se ha creado la OT N° ' . $ot->getNroOT());
            return new Response($ot->getNroOT());
        }
        else {
            return new Response('ERROR');
        }
    }

    /**
     * @Route("/renderAddEquipoDetalle", name="render_add_equipo_detalle")
     * @Method("GET")
     */
    public function renderAddEquipoDetalle(Request $request) {
        $dptoId = $request->get('sol');
        $id = $request->get('id');
        $op = $request->get('op');
        if ($op == 'req') {
            $detalle = new RequerimientoDetalle();
            $detForm = new RequerimientoDetalleType();
        }
        else {
            $detalle = new OrdenTrabajoDetalle();
            $detForm = new OrdenTrabajoDetalleType();
        }
        $form = $this->createForm($detForm, $detalle, array(
            'action' => $this->generateUrl('soporte_requerimiento_detalle_create', array('id' => $id)),
            'method' => 'POST',
        ));

        $html = $this->renderView('AppBundle:Requerimiento:partial-add-detalle.html.twig',
                array('entity' => $detalle, 'op' => $op, 'dptoId' => $dptoId, 'form' => $form->createView()));
        return new Response($html);
    }

    /**
     * @Route("/createAddDetalle/{id}", name="soporte_requerimiento_detalle_create")
     * @Method("POST")
     * @Template()
     */
    public function createAddDetalleAction(Request $request, $id) {
        //UtilsController::haveAccess($this->getUser(), 'requerimiento_new');
        $em = $this->getDoctrine()->getManager();
        $op = $request->get('op');
        if ($op == 'req') {
            $requerimiento = $em->getRepository('AppBundle:Requerimiento')->find($id);
            $detalle = new RequerimientoDetalle();
            $detalle->setRequerimiento($requerimiento);
            $detForm = new RequerimientoDetalleType();
            $reqId = $id;
        }
        else {
            $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
            $detalle = new OrdenTrabajoDetalle();
            $detalle->setOrdenTrabajo($ot);
            $detForm = new OrdenTrabajoDetalleType();
            $reqId = $ot->getRequerimiento()->getId();
            $session = $this->get('session');
            $session->set('otid', $ot->getId());
        }
        $form = $this->createForm($detForm, $detalle, array(
            'action' => $this->generateUrl('soporte_requerimiento_detalle_create', array('id' => $id)),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $em->getConnection()->setAutoCommit(false);
                // verificar que el equipo no esté en otro requerimiento, u ot
                $equipo = $detalle->getEquipo();
                if ($equipo) {
                    $enOrdenAbierta = $em->getRepository('AppBundle:Equipo')->checkEnOrdenAbierta($equipo->getId());
                    if ($equipo->getEnRequerimientoAbierto() || $enOrdenAbierta) {
                        $this->addFlash('danger', 'Este equipo ya se encuentra en un requerimiento abierto');
                        $em->getConnection()->rollback();
                    }
                    else {
                        //                    $em->flush();
                        // mover el equipo y cambiar estado
                        //$estado = $em->getRepository('ConfigBundle:Estado')->findOneByAbreviatura('RP');
                        $detalle->setEstadoOriginal($equipo->getEstado());
                        //$equipo->setEstado($estado);
                        //$servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
                        $ubicOrignal = $equipo->getUbicacionActual();
                        $detalle->setEquipoUbicacionOriginal($ubicOrignal);
                        // quitar equipo actual para agregar el nuevo
                        //self::unsetUbicaciones($em, $equipo->getId());
                        //
                        /* /*$ubicacion = new EquipoUbicacion();
                          $ubicacion->setActual(1);
                          $ubicacion->setDepartamento($servTecnico);
                          $ubicacion->setEdificio($servTecnico->getEdificio());
                          $ubicacion->setUbicacion($servTecnico->getEdificio()->getUbicacion());
                          $ubicacion->setPiso($servTecnico->getPisos()[0]);
                          $ubicacion->setFechaEntrega(new \DateTime());
                          $ubicacion->setConceptoEntrega($em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByAbreviatura('ST'));

                          if ($op == 'req') {
                          $ubicacion->setRequerimientoDetalle($detalle);
                          $detalle->setEquipoUbicacionRequerimiento($ubicacion);
                          }
                          else {
                          $ubicacion->setOrdenTrabajoDetalle($detalle);
                          $detalle->setEquipoUbicacionOrdenTrabajo($ubicacion);
                          }
                          $equipo->addUbicacion($ubicacion);
                          $em->persist($equipo); */
                        $em->persist($detalle);
                        $em->flush();
                        $em->getConnection()->commit();
                        $this->addFlash('success', 'El equipo fue agregado correctamente.');
                    }
                }
            }
            catch (Exception $ex) {
                $this->addFlash('danger', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        if ($op == 'tar') {
            return $this->redirectToRoute('soporte_ordentrabajo_tareas', array('id' => $id));
        }
        else {
            return $this->redirectToRoute('soporte_requerimiento_edit', array('id' => $reqId));
        }
    }

    /**
     * @Route("/deleteDetalle", name="delete_requerimiento_detalle")
     * @Method("POST")
     * @Template()
     */
    public function deleteDetalleAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'requerimiento_delete');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $detalle = $em->getRepository('AppBundle:RequerimientoDetalle')->find($id);
        $equipo = $detalle->getEquipo();
        $em->getFilters()->disable('softdeleteable');
        try {
            $em->getConnection()->beginTransaction();
            $em->getConnection()->setAutoCommit(false);
            if ($detalle->getEstadoOriginal()) {
                $equipo->setEstado($detalle->getEstadoOriginal());
            }
            if ($detalle->getEquipoUbicacionOriginal()) {
                if ($detalle->getEquipoUbicacionRequerimiento()) {
                    $detalle->getEquipoUbicacionOriginal()->setActual(1);
                    $em->persist($detalle);
                    //$ubic = $detalle->getEquipoUbicacionRequerimiento();
                    //$em->remove($ubic);
                }
                else {
                    $origen = $detalle->getEquipoUbicacionOriginal();
                    // quitar equipo actual para agregar el nuevo
                    self::unsetUbicaciones($em, $equipo->getId());
                    //
                    $ubicacion = new EquipoUbicacion();
                    $ubicacion->setActual(1);
                    $ubicacion->setDepartamento($origen->getDepartamento());
                    $ubicacion->setEdificio($origen->getDepartamento()->getEdificio());
                    $ubicacion->setUbicacion($origen->getDepartamento()->getEdificio()->getUbicacion());
                    $ubicacion->setPiso($origen->getPiso());
                    $ubicacion->setFechaEntrega(new \DateTime());
                    $equipo->addUbicacion($ubicacion);
                }
            }
            $em->persist($equipo);
            //$em->flush();
            $em->remove($detalle);
            $em->flush();
            $em->getConnection()->commit();
            $this->addFlash('success', 'El equipo fue quitado correctamente.');
            return new Response('OK');
        }
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return new Response($ex->getMessage());
        }
    }

    /*
     * Asignar requerimiento a técnico
     */

    private function asignarRequerimiento($requerimiento, $tecnico, $em) {
        // si se asignó a un técnico generar orden de trabajo.
        $ot = new OrdenTrabajo();
        $ot->setFechaOrden(new \DateTime());
        $ot->setTecnico($tecnico);
        $ot->setEstado('ABIERTO');
        $ot->setRequerimiento($requerimiento);
        $ot->setJira($requerimiento->getJira());
        $ot->setNroOrden(count($requerimiento->getOrdentrabajoAsociadas()) + 1);
        $ot->setDescripcion($requerimiento->getDescripcion());
        $ot->setAltaPrioridad($requerimiento->getAltaPrioridad());
        if ($requerimiento->getDetalles()) {
            foreach ($requerimiento->getDetalles() as $detreq) {
                if (count($detreq->getOrdenTrabajoDetalle()) == 0) {
                    $det = new OrdenTrabajoDetalle();
                    $det->setEquipo($detreq->getEquipo());
                    $det->setEstadoOriginal($detreq->getEstadoOriginal());
                    $det->setEquipoUbicacionOriginal($detreq->getEquipoUbicacionOriginal());
                    $det->setDescripcion($detreq->getDescripcion());
                    $det->setRequerimientoDetalle($detreq);
                    if ($detreq->getEquipoUbicacionRequerimiento()) {
                        $ubicacion = $detreq->getEquipoUbicacionRequerimiento();
                        $ubicacion->setOrdenTrabajoDetalle($det);
                    }
                    $ot->addDetalle($det);
                }
            }
        }
        $em->persist($ot);
        $em->flush();
        // generar mensajería
        $textoMensaje = 'Se ha asignado la OT N° ' . $ot->getNroOT();
        $asunto = 'Nueva OT';
        $mensaje = new Mensajeria();
        $mensaje->setDestinatario($tecnico);
        $mensaje->setAsunto($asunto);
        $mensaje->setMensaje($textoMensaje);
        $em->persist($mensaje);
        $em->flush();
        return $ot;
    }

    public static function unsetUbicaciones($em, $id) {
        $enable = 0;
        if ($em->getFilters()->isEnabled('softdeleteable')) {
            $enable = 1;
            $em->getFilters()->disable('softdeleteable');
        }
        $res = $em->getRepository('AppBundle:Equipo')->unsetUbicaciones($id);
        if ($enable) {
            $em->getFilters()->enable('softdeleteable');
        }
        return $res;
    }

}