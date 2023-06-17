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
use AppBundle\Controller\RequerimientoController;
use AppBundle\Entity\OrdenTrabajo;
use AppBundle\Entity\OrdenTrabajoDetalle;
use AppBundle\Entity\Tarea;
use AppBundle\Form\TareaType;
use AppBundle\Form\EstadoUbicacionType;
use AppBundle\Entity\EquipoUbicacion;
use AppBundle\Entity\Mensajeria;
use AppBundle\Entity\Documentacion;
use AppBundle\Form\DocumentacionType;
use AppBundle\Entity\InsumoEntrega;
use AppBundle\Entity\InsumoEntregaDetalle;
/* use AppBundle\Entity\Requerimiento;
  use AppBundle\Form\RequerimientoType;
  use AppBundle\Form\OrdenTrabajoType;
  use AppBundle\Form\OrdenTrabajoTareasType;
 */

/**
 * @Route("/soporte")
 */
class OrdenTrabajoController extends Controller {

    /**
     * @Route("/", name="soporte_ordentrabajo")
     * @Method("GET")
     * @Template("AppBundle:OrdenTrabajo:index.html.twig")
     */
    public function ordenTrabajoAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'ordentrabajo');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $userId = $this->getUser()->getId();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_ordentrabajo');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'idUbicacion' => $request->get('idUbicacion'),
                    'idEdificio' => $request->get('idEdificio'),
                    'idDepartamento' => $request->get('idDepartamento'),
                    'idTipoIncidencia' => $request->get('idTipoIncidencia'),
                    'idTecnico' => $request->get('idTecnico'),
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
                        'idTipoIncidencia' => $sessionFiltro['idTipoIncidencia'],
                        'idTecnico' => $sessionFiltro['idTecnico'],
                        'estado' => $sessionFiltro['estado'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('idDepartamento' => 0, 'idEdificio' => 0, 'idUbicacion' => 0, 'idTecnico' => 0,
                        'idTipoIncidencia' => 0, 'estado' => 'ABIERTO', 'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        if ($this->getUser()->getRol()->getPermiso('ordentrabajo_own')) {
            $tecnicos[] = array('id' => $userId, 'nombre' => $this->getUser()->getNombre());
            $filtro['idTecnico'] = $userId;
        }
        else {
            $tecnicos = $em->getRepository('ConfigBundle:Usuario')->findTecnicos();
        }
        $session->set('filtro_ordentrabajo', $filtro);
        $entities = $em->getRepository('AppBundle:OrdenTrabajo')->findOTByCriteria($filtro, $userId);

        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);

        $edificios = null;
        $departamentos = null;
        $tipoIncidencias = $em->getRepository('ConfigBundle:TipoSoporte')->findAll();
        if ($filtro['idUbicacion']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findByUbicacion($filtro['idUbicacion']);
            if ($filtro['idEdificio']) {
                $departamentos = $em->getRepository('ConfigBundle:Departamento')->findByEdificio($filtro['idEdificio']);
            }
        }
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->ordentrabajoDeleteForm($entity->getId())->createView();
        }
        return array(
            'entities' => $entities,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'tiposIncidencia' => $tipoIncidencias,
            'tecnicos' => $tecnicos,
            'filtro' => $filtro,
            'deleteForms' => $deleteForms
        );
    }

    /**
     * @Route("/{id}/edit", name="soporte_ordentrabajo_edit")
     * @Method("GET")
     * @Template()
     */
    public function ordentrabajoEditAction($id) {
        $em = $this->getDoctrine()->getManager();
        $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        $session = $this->get('session');
        $session->set('otid', $id);
        return $this->redirectToRoute('soporte_requerimiento_edit', array('id' => $ot->getRequerimiento()->getId()));
    }

    /**
     * @Route("/{id}/show", name="soporte_ordentrabajo_show")
     * @Method("GET")
     * @Template()
     */
    public function ordentrabajoShowAction($id) {
        UtilsController::haveAccess($this->getUser(), 'ordentrabajo');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la orden de trabajo.');
        }
        $html = $this->renderView('AppBundle:OrdenTrabajo:show.html.twig',
                array('entity' => $entity));
        return new Response($html);
    }

    /**
     * @Route("/{id}/reabrir", name="soporte_ordentrabajo_reabrir")
     * @Method("GET")
     * @Template()
     */
    public function ordentrabajoReabrirAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        $entity->setEstado('ABIERTO');
        if ($entity->getRequerimiento()) {
            $req = $entity->getRequerimiento();
            $req->setEstado('ASIGNADO');
            $em->persist($req);
        }
        $em->persist($entity);
        // GENERAR TAREA PARA REGISTRAR REAPERTURA
        $tarea = new Tarea();
        $hoy = new \DateTime();
        $tarea->setFecha($hoy);
        $tarea->setOrdenTrabajo($entity);
        $tipo = $em->getRepository('ConfigBundle:TipoTarea')->find(1);
        $tarea->setTipoTarea($tipo);
        $tarea->setDescripcion('Reapertura de OT');
        $em->persist($tarea);
        $em->flush();
        // Si es el tecnico asignado quien reabre dirigir a tareas
        if ($this->getUser()->getId() == $entity->getTecnico()->getId()) {
            return $this->redirectToRoute('soporte_ordentrabajo_tareas', array('id' => $entity->getId()));
        }
        else {
            $session = $this->get('session');
            $session->set('otid', $entity->getId());
            return $this->redirectToRoute('soporte_requerimiento_edit', array('id' => $entity->getRequerimiento()->getId()));
        }
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function ordentrabajoDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('soporte_ordentrabajo_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm();
    }

    /**
     * @Route("/deleteOrdentrabajo/{id}", name="delete_ordentrabajo")
     * @Method("GET")
     */
    public function deleteOrdentrabajoAction($id) {
        UtilsController::haveAccess($this->getUser(), 'ordentrabajo_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        try {
            $em->getConnection()->beginTransaction();
            $entity = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('No existe la orden de trabajo.');
            }
            $session = $this->get('session');
            $session->set('otid', $entity->getId());

            if (is_null($entity->getDeletedAt())) {
                //forzar el guardado de ultima fecha de modificaciÃ³n antes de softdelete
                $em->getFilters()->enable('softdeleteable');
                $entity->setUpdated(new \DateTime());
                $entity->setEstado('CANCELADO');
                $em->persist($entity);
                $em->flush();
            }
            else {
                if ($entity->getEstado() == 'CANCELADO') {
                    $entity->setEstado('ABIERTO');
                    $em->persist($entity);
                    $em->flush();
                }
            }
            $em->remove($entity);
            $em->flush();
            $em->getConnection()->commit();
            $this->addFlash('success', 'La orden de trabajo fue cancelada!');
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
        }
        return $this->redirectToRoute('soporte_requerimiento_edit', array('id' => $entity->getRequerimiento()->getId()));
    }

    /**
     * @Route("/delete/{id}", name="soporte_ordentrabajo_delete")
     * @Method("DELETE")
     */
    public function ordentrabajoDeleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'ordentrabajo_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
                if (!$entity) {
                    throw $this->createNotFoundException('No existe la orden de trabajo.');
                }
                if (is_null($entity->getDeletedAt())) {
                    //forzar el guardado de ultima fecha de modificaciÃ³n antes de softdelete
                    $em->getFilters()->enable('softdeleteable');
                    $entity->setUpdated(new \DateTime());
                    $entity->setEstado('CANCELADO');
                    $em->persist($entity);
                    $em->flush();
                }
                /* if( $entity->getRequerimiento() ){
                  $req = $entity->getRequerimiento();
                  $req->setEstado('SIN ASIGNAR');
                  $em->persist($req);
                  $em->flush();
                  } */
                $em->remove($entity);
                $em->flush();
                $em->getConnection()->commit();
                $this->addFlash('success', 'La orden de trabajo fue cancelada!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return $this->redirectToRoute('soporte_ordentrabajo');
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('soporte_ordentrabajo_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printListadoOrdentrabajo", name="print_listado_ordentrabajo")
     * @Method("POST")
     * @Template()
     */
    public function printListadoOrdentrabajoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $session = $this->get('session');
        $filtro = $session->get('filtro_ordentrabajo');
        $tecnico = $em->getRepository('ConfigBundle:Usuario')->find($filtro['idTecnico']);
        $ubicacion = $em->getRepository('ConfigBundle:Ubicacion')->find($filtro['idUbicacion']);
        $edificio = $em->getRepository('ConfigBundle:Edificio')->find(($filtro['idEdificio']) ? $filtro['idEdificio'] : 0);
        $departamento = $em->getRepository('ConfigBundle:Departamento')->find(($filtro['idDepartamento']) ? $filtro['idDepartamento'] : 0);
        $periodo = UtilsController::ultimoMesParaFiltro($filtro['desde'], $filtro['hasta']);
        $arrayFiltro = array($tecnico ? $tecnico->getNombre() : 'Todos', $filtro['estado'] ? $filtro['estado'] : 'Todos',
            $ubicacion ? $ubicacion->getAbreviatura() : 'Todos', $edificio ? $edificio->getNombre() : 'Todos',
            $departamento ? $departamento->getNombre() : 'Todos', $periodo['desde'], $periodo['hasta']);
        $hoy = new \DateTime();
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/pdf_logo.png';
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('AppBundle:OrdenTrabajo:listado.pdf.twig', array('items' => json_decode($items), 'filtro' => $arrayFiltro,
            'logo' => $logo1, 'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);

        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_requerimientos_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("/pdfOrdentrabajo.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ordentrabajo")
     * @Method("GET")
     */
    public function pdfOrdentrabajoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        $otNro = $ot->getNroOT();
        $fecha = UtilsController::longDateSpanish($ot->getFechaOrden());
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:OrdenTrabajo:ordentrabajo.pdf.twig',
                array('ot' => $ot, 'logo' => $logo1, 'fecha' => $fecha), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=ordentrabajo_' . $otNro . '.pdf'));
    }

    /**
     * @Route("/pdfOrdentrabajoResumen.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ordentrabajo_resumen")
     * @Method("GET")
     */
    public function pdfOrdentrabajoResumenAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        $otNro = $ot->getNroOT();
        $fecha = UtilsController::longDateSpanish($ot->getFechaOrden());
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        $qr = __DIR__ . '/../../../web/uploads/qr_survey_ls.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:OrdenTrabajo:ordentrabajo_resumen.pdf.twig',
                array('ot' => $ot, 'logo' => $logo1, 'qr' => $qr, 'fecha' => $fecha), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=ordentrabajo_' . $otNro . '.pdf'));
    }

    /**
     * TAREAS DE SOPORTE
     */

    /**
     * @Route("/{id}/tareas", name="soporte_ordentrabajo_tareas")
     * @Method("GET")
     * @Template("AppBundle:OrdenTrabajo:tareas.html.twig")
     */
    public function ordentrabajoTareasAction($id) {
        //UtilsController::haveAccess($this->getUser(),'ordentrabajo_new');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la orden de trabajo.');
        }
        if ($this->getUser()->getAccess('ordentrabajo_own') && $this->getUser()->getId() != $entity->getTecnico()->getId()) {
            throw new AccessDeniedException('No posee permiso para acceder a esta página!');
        }
        $tecnicos = $em->getRepository('ConfigBundle:Usuario')->findTecnicos();
        return array(
            'entity' => $entity,
            'tecnicos' => $tecnicos
        );
    }

    /**
     * @Route("/{id}/renderAddTarea/{tipo}", name="render_add_tarea")
     * @Method("GET")
     */
    public function renderAddTareaAction(Request $request, $id, $tipo) {
        //UtilsController::haveAccess($this->getUser(),'proveedor_new');
        $em = $this->getDoctrine()->getManager();
        $equipos = $request->get('eq');
        $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);
        $tipoTarea = $em->getRepository('ConfigBundle:TipoTarea')->findOneByAbreviatura($tipo);
        $tarea = new Tarea();
        $hoy = new \DateTime();
        $tarea->setFecha($hoy);
        $tarea->setOrdenTrabajo($ot);
        $tarea->setTipoTarea($tipoTarea);
        if ($equipos) {
            foreach ($ot->getDetalles() as $det) {
                if (in_array($det->getId(), $equipos) && !$det->getEntregado()) {
                    $tarea->addOrdenTrabajoDetalle($det);
                }
            }
        }
        $form = $this->createForm(new TareaType(), $tarea, array(
            'action' => $this->generateUrl('create_tarea'),
            'method' => 'POST',
        ));
        $form->get('hora')->setData($hoy->format('H:i'));

        switch ($tipo) {
            case 'NT':
            case 'TS':
                // NT= Nota
                // TS= Terminar Soporte
                $html = $this->renderView('AppBundle:OrdenTrabajo:partial-add-tarea.html.twig',
                        array('entity' => $tarea, 'form' => $form->createView(), 'ot' => $ot)
                );
                break;
            case 'DS':
                // DS= Derivacion de OT
                $tecnicos = $em->getRepository('ConfigBundle:Usuario')->findTecnicos();
                $html = $this->renderView('AppBundle:OrdenTrabajo:partial-add-tarea.html.twig',
                        array('entity' => $tarea, 'form' => $form->createView(), 'ot' => $ot,
                            'tecnicos' => $tecnicos)
                );
                break;
            case 'RE':
                // RE= Reubicacion de Equipo
                $estados = $em->getRepository('ConfigBundle:Estado')->findAll();
                $form->get('textoAdicional')->setData('Se deja constancia que al momento de recibir el/los equipo/s aquí especificados se realizaron las pruebas de funcionamiento y se encontraba en buen estado.');
                $html = $this->renderView('AppBundle:OrdenTrabajo:partial-add-tarea.html.twig',
                        array('entity' => $tarea, 'form' => $form->createView(), 'ot' => $ot,
                            'estados' => $estados)
                );
                break;
            case 'SI':
                // SI= Solicitud de Hardware
                $html = $this->renderView('AppBundle:OrdenTrabajo:partial-add-tarea-insumo.html.twig',
                        array('entity' => $tarea, 'form' => $form->createView(), 'ot' => $ot, 'subclase' => 'HARDWARE')
                );
                break;
            case 'PI':
                // PI= Pedido de Insumo
                $html = $this->renderView('AppBundle:OrdenTrabajo:partial-add-tarea-insumo.html.twig',
                        array('entity' => $tarea, 'form' => $form->createView(), 'ot' => $ot, 'subclase' => 'INSUMO')
                );
                break;
            case 'CE':
                // CE= Reemplazo de Equipo
                $estados = $em->getRepository('ConfigBundle:Estado')->findAll();
                /* $servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
                  if ($servTecnico) {
                  $form->get('ubicacion')->get('ubicacion')->setData($servTecnico->getEdificio()->getUbicacion());
                  $form->get('ubicacion')->get('edificio')->setData($servTecnico->getEdificio());
                  $form->get('ubicacion')->get('departamento')->setData($servTecnico);
                  $form->get('ubicacion')->get('piso')->setData($servTecnico->getPisos()[0]);
                  } */
                // ubicacion anterior del equipo a retirar.
                $ubicant = $tarea->getOrdenTrabajoDetalles()[0]->getEquipoUbicacionOriginal();
                if ($ubicant) {
                    $form->get('ubicacionNueva')->get('ubicacion')->setData($ubicant->getUbicacion());
                    $form->get('ubicacionNueva')->get('edificio')->setData($ubicant->getEdificio());
                    $form->get('ubicacionNueva')->get('departamento')->setData($ubicant->getDepartamento());
                    $form->get('ubicacionNueva')->get('piso')->setData($ubicant->getPiso());
                }
                $form->get('textoAdicional')->setData('Se deja constancia que al momento de recibir el/los equipo/s aquí especificados se realizaron las pruebas de funcionamiento y se encontraba en buen estado.');
                $html = $this->renderView('AppBundle:OrdenTrabajo:partial-add-tarea-recambio.html.twig',
                        array('entity' => $tarea, 'form' => $form->createView(), 'ot' => $ot, 'estados' => $estados)
                );
                break;
        }
        return new Response($html);
    }

    /**
     * @Route("/ajax/createTarea", name="create_tarea")
     * @Method("POST")
     * @Template()
     */
    public function createTareaAction(Request $request) {
        $entity = new Tarea();
        $data = $request->get('appbundle_tarea');
        $em = $this->getDoctrine()->getManager();
        $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($data['ordenTrabajo']);
        $entity->setOrdenTrabajo($ot);
        $tipoTarea = $em->getRepository('ConfigBundle:TipoTarea')->find($data['tipoTarea']);
        $entity->setTipoTarea($tipoTarea);
        /* if( $tipoTarea->getAbreviatura()=='CE' ){
          $data['ordenTrabajoDetalles'] = explode(' ',$data['ordenTrabajoDetalles']);
          $data['equipoNuevo'] = explode(' ',$data['equipoNuevo']);
          } */
        $form = $this->createForm(new TareaType(), $entity, array(
            'action' => $this->generateUrl('create_tarea'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        $url = null;
        $salida = '';
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $em->getConnection()->setAutoCommit(false);
                $entity->setFecha(new \DateTime(UtilsController::toAnsiDate($data['fecha']) . ' ' . $data['hora'] . ':00'));
                $em->persist($entity);
                $em->flush();
                $salida = 'OK';
                // verificar tipo de tarea
                /*
                 * DERIVAR SOPORTE
                 */
                if ($entity->getTipoTarea()->getAbreviatura() == 'DS') {
                    // derivar a otro técnico
                    $tecnicoNuevo = $em->getRepository('ConfigBundle:Usuario')->find($request->get('tecnicoId'));
                    $nueva = false;
                    if ($ot->getCantDetallesSinEntregar() > 0) {
                        // si la OT tiene detalles
                        $cantTar = count($entity->getOrdenTrabajoDetalles());
                        if ($cantTar > 0 && $cantTar < $ot->getCantDetallesSinEntregar()) {
                            // si seleccionaron equipos y no son todos.
                            $nueva = true;
                        }
                    }
                    if ($nueva) {
                        // generar otra OT
                        $otNueva = new OrdenTrabajo();
                        $otNueva->setFechaOrden(new \DateTime());
                        $otNueva->setTecnico($tecnicoNuevo);
                        $otNueva->setEstado('ABIERTO');
                        $otNueva->setRequerimiento($ot->getRequerimiento());
                        $otNueva->setJira($ot->getJira());
                        $otNueva->setNroOrden(count($ot->getRequerimiento()->getOrdentrabajoAsociadas()) + 1);
                        $otNueva->setDescripcion($entity->getDescripcion());
                        $otNueva->setAltaPrioridad($ot->getAltaPrioridad());
                        foreach ($entity->getOrdenTrabajoDetalles() as $deteq) {
                            $det = new OrdenTrabajoDetalle();
                            $det->setEquipo($deteq->getEquipo());
                            $det->setEstadoOriginal($deteq->getEstadoOriginal());
                            $det->setEquipoUbicacionOriginal($deteq->getEquipoUbicacionOriginal());
                            $det->setDescripcion($deteq->getDescripcion());
                            $det->setRequerimientoDetalle($deteq->getRequerimientoDetalle());
                            $otNueva->addDetalle($det);
                            $deteq->setEntregado(true);
                        }
                        $em->persist($otNueva);
                        $em->flush();
                        $entity->setDescripcion('Derivado a OT N° ' . $otNueva->getNroOT());
                        $em->persist($entity);
                        $textoMensaje = 'Se ha asignado la OT N° ' . $otNueva->getNroOT();
                        $asunto = 'Nueva OT';
                        $salida = 'OK';
                    }
                    else {
                        // remover equipos x tarea para registrar como tarea general
                        foreach ($entity->getOrdenTrabajoDetalles() as $deteq) {
                            $entity->removeOrdenTrabajoDetalle($deteq);
                        }
                        $em->persist($entity);
                        $ot->setTecnico($tecnicoNuevo);
                        $textoMensaje = 'Se le ha derivado la OT N° ' . $ot->getNroOT();
                        $asunto = 'OT Derivada';
                        $salida = 'OUT';
                    }
                    $em->persist($ot);
                    //generar mensaje
                    $mensaje = new Mensajeria();
                    $mensaje->setDestinatario($tecnicoNuevo);
                    $mensaje->setAsunto($asunto);
                    $mensaje->setMensaje($textoMensaje);
                    $em->persist($mensaje);
                    $em->flush();
                }
                /*
                 * TERMINAR SOPORTE
                 */
                if ($entity->getTipoTarea()->getAbreviatura() == 'TS') {
                    if (!$entity->getDescripcion()) {
                        $entity->setDescripcion('Se registra como cerrado el soporte.');
                    }
                    // marcar el soporte para verificacion
                    $ot->setEstado('CERRADO');
                    // si hay equipos sin ubicar agregar tarea de reubicacion
                    $reubicar = false;
                    $tarea = new Tarea();
                    $hoy = new \DateTime();
                    $tarea->setFecha($hoy);
                    $tarea->setOrdenTrabajo($ot);
                    $tipo = $em->getRepository('ConfigBundle:TipoTarea')->find(1);
                    $tarea->setTipoTarea($tipo);
                    // restaurar ubicación a equipos
                    $ubic_nueva = null;
                    /* foreach ($ot->getDetalles() as $det) {
                      if (!$det->getEntregado()) {
                      //$reubicar = true;
                      $equipo = $det->getEquipo();
                      if ($det->getEstadoOriginal()) {
                      $equipo->setEstado($det->getEstadoOriginal());
                      }
                      RequerimientoController::unsetUbicaciones($em, $equipo->getId());
                      $ubic_nueva = new EquipoUbicacion();
                      $ubic_nueva->setFechaEntrega(new \DateTime());
                      $ubic_nueva->setConceptoEntrega($em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByAbreviatura('ST'));
                      if ($det->getEquipoUbicacionOriginal()) {
                      $departamento = $det->getEquipoUbicacionOriginal()->getDepartamento();
                      $piso = $det->getEquipoUbicacionOriginal()->getPiso();
                      $redIp = $det->getEquipoUbicacionOriginal()->getRedIp();
                      }
                      else {
                      // buscar solicitante del requerimiento
                      $departamento = $ot->getRequerimiento()->getSolicitante();
                      $piso = $departamento->getPisos()[0];
                      $redIp = null;
                      }
                      $ubic_nueva->setDepartamento($departamento);
                      $ubic_nueva->setEdificio($departamento->getEdificio());
                      $ubic_nueva->setUbicacion($departamento->getEdificio()->getUbicacion());
                      $ubic_nueva->setPiso($piso);
                      $ubic_nueva->setRedIp($redIp);
                      $ubic_nueva->setActual(1);
                      $equipo->addUbicacion($ubic_nueva);
                      $em->persist($equipo);
                      // ver si hace falta dejar sin entregar para volver a reubicar al reabrir la ot
                      $det->setEntregado(1);
                      $em->persist($det);
                      // agregar equipo en tarea de reubicación
                      $tarea->addOrdenTrabajoDetalle($det);
                      }
                      } */
                    /* if ($reubicar) {
                      // si se reubicaron equipos guardar tarea
                      $tarea->setEquipoUbicacionFinal($ubic_nueva->getDepartamento());
                      $tarea->setDescripcion('Reubicación a <strong>' . $ubic_nueva->getTexto() . '</strong> Estado: <strong>' . $det->getEstadoOriginal() . '</strong>');
                      $em->persist($tarea);
                      } */
                    $em->persist($ot);
                    //if ($ubic_nueva)
                    //    $entity->setEquipoUbicacionFinal($ubic_nueva->getDepartamento());
                    if ($ot->getRequerimiento()) {
                        $req = $ot->getRequerimiento();
                        $cerrar = true;
                        foreach ($req->getOrdentrabajoAsociadas() as $otAsociada) {
                            if ($otAsociada->getEstado() != 'CERRADO') {
                                $cerrar = false;
                            }
                        }
                        if ($cerrar) {
                            $req->setEstado('FINALIZADO');
                            $req->setDescripcionFinalizacion($entity->getTextoAdicional());
                            $em->persist($req);
                        }
                    }
                    $em->flush();
                    $salida = 'OUT';
                }
                if ($entity->getTipoTarea()->getAbreviatura() == 'SI') {
                    // solicitar hardware
                    $text = '';
                    foreach ($entity->getInsumos() as $insumoxTarea) {
                        $insumoxTarea->setTarea($entity);
                        $em->flush();
                        $text = $text . ' [ ' . $insumoxTarea->getCantidad() . ' - ' . $insumoxTarea->getDescripcion() . ' ] ';
                    }
                    $entity->setDescripcion($text);
                    $em->flush();
                    $salida = 'OK';
                }
                if ($entity->getTipoTarea()->getAbreviatura() == 'PI') {
                    // solicitar insumo
                    $text = '';
                    // Generar pedido de insumo a mesa de entradas
                    $pedido = new InsumoEntrega();
                    $pedido->setFecha(new \DateTime());
                    $pedido->setEstado('PENDIENTE');
                    $pedido->setResponsable($this->getUser()->getNombre());
                    //$servTecnico = $em->getRepository('ConfigBundle:Departamento')->findOneByServicioTecnico(1);
                    $pedido->setSolicitante($entity->getOrdenTrabajo()->getRequerimiento()->getSolicitante());

                    foreach ($entity->getInsumos() as $insumoxTarea) {
                        $insumoxTarea->setTarea($entity);
                        $detped = new InsumoEntregaDetalle();
                        $detped->setInsumo($insumoxTarea->getInsumo());
                        $detped->setCantidad($insumoxTarea->getCantidad());
                        $detped->setInsumoxTarea($insumoxTarea);
                        $pedido->addDetalle($detped);
                        $em->flush();
                        $text = $text . ' [ ' . $insumoxTarea->getCantidad() . ' - ' . $insumoxTarea->getDescripcion() . ' ] ';
                    }
                    $entity->setDescripcion($text);
                    $em->persist($pedido);
                    $em->flush();
                    $salida = 'OK';
                }
                /*
                 * REUBICACION DE EQUIPO
                 */
                if ($entity->getTipoTarea()->getAbreviatura() == 'RE') {
                    $descripciones = $request->get('descripcion');
                    $estado = $em->getRepository('ConfigBundle:Estado')->find($data['estadoId']);
                    $ubic = $data['ubicacion'];
                    $concepto_entrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->find($ubic['conceptoEntrega']);
                    $entity->setConceptoEntrega($concepto_entrega);
                    foreach ($entity->getOrdenTrabajoDetalles() as $key => $det) {
                        $equipo = $det->getEquipo();
                        $equipo->setEstado($estado);
                        if (trim($descripciones[$key])) {
                            $equipo->setNombre(trim($descripciones[$key]));
                        }
                        $ubic_actual = $equipo->getUbicacionActual();
                        $ubic_actual->setActual(0);
                        $ubic_nueva = new EquipoUbicacion();
                        $ubic_nueva->setFechaEntrega(new \DateTime(UtilsController::toAnsiDate($ubic['fechaEntrega']) . ' 00:00:00'));
                        $ubic_nueva->setConceptoEntrega($concepto_entrega);
                        $ubic_nueva->setObservaciones($ubic['observaciones']);
                        $departamento = $em->getRepository('ConfigBundle:Departamento')->find($ubic['departamento']);
                        $ubic_nueva->setDepartamento($departamento);
                        $ubic_nueva->setEdificio($departamento->getEdificio());
                        $ubic_nueva->setUbicacion($departamento->getEdificio()->getUbicacion());
                        $piso = $em->getRepository('ConfigBundle:Piso')->find($ubic['piso']);
                        $ubic_nueva->setPiso($piso);
                        $ubic_nueva->setActual(1);
                        $equipo->addUbicacion($ubic_nueva);
                        $em->persist($equipo);
                        $det->setEntregado(true);
                        $det->setEquipoUbicacionFinal($ubic_nueva);
                        $em->persist($det);
                    }
                    $entity->setEquipoUbicacionFinal($ubic_nueva->getDepartamento());
                    $entity->setDescripcion('Reubicación a <strong>' . $ubic_nueva->getTexto() . '</strong> Estado: <strong>' . $estado->getNombre() . '</strong>');
                    $em->persist($entity);
                    $em->flush();
                    $salida = 'OK';
                }
                if ($entity->getTipoTarea()->getAbreviatura() == 'CE') {
                    // reemplazo o cambio de equipo
                    $descr_out = $request->get('desc_out');
                    $eqid_out = array();
                    $descr_in = $request->get('desc_in');
                    $eqid_in = $request->get('eqid_in');

                    // datos para equipos que se retiran
                    $estado = $em->getRepository('ConfigBundle:Estado')->find($data['estadoId']);
                    $ubic = $data['ubicacion'];
                    /* EQUIPOS SALIENTES - OUT */
                    foreach ($entity->getOrdenTrabajoDetalles() as $key => $det) {
                        // equipos a retirar
                        $det->getTipoRecambio('OUT');
                        $equipo = $det->getEquipo();
                        array_push($eqid_out, $equipo->getId());
                        $equipo->setEstado($estado);
                        $equipo->setNombre($descr_out[$key] . '_2');
                        $ubic_actual = $equipo->getUbicacionActual();
                        $ubic_actual->setActual(0);

                        $ubic_nueva = new EquipoUbicacion();
                        $ubic_nueva->setFechaEntrega($entity->getFecha());
                        $concepto_entrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->find($ubic['conceptoEntrega']);
                        $ubic_nueva->setConceptoEntrega($concepto_entrega);
                        $ubic_nueva->setObservaciones($ubic['observaciones']);
                        $departamento = $em->getRepository('ConfigBundle:Departamento')->find($ubic['departamento']);
                        $ubic_nueva->setDepartamento($departamento);
                        $ubic_nueva->setEdificio($departamento->getEdificio());
                        $ubic_nueva->setUbicacion($departamento->getEdificio()->getUbicacion());
                        $piso = $em->getRepository('ConfigBundle:Piso')->find($ubic['piso']);
                        $ubic_nueva->setPiso($piso);
                        $ubic_nueva->setActual(1);
                        $equipo->addUbicacion($ubic_nueva);
                        $em->persist($equipo);
                        $det->setEntregado(true);
                        $det->setEquipoUbicacionFinal($ubic_nueva);
                        $em->persist($det);
                    }
                    // datos para equipos que se entregan
                    $estadoNuevo = $em->getRepository('ConfigBundle:Estado')->find($data['estadoNuevoId']);
                    $ubicNueva = $data['ubicacionNueva'];
                    $concepto_entrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->find($ubicNueva['conceptoEntrega']);
                    $entity->setConceptoEntrega($concepto_entrega);
                    /* EQUIPOS QUE QUEDAN OPERATIVOS  - IN */
                    foreach ($eqid_in as $key => $new) {
                        $eqNuevo = $em->getRepository('AppBundle:Equipo')->find($new);
                        // crear nuevo detalle de OT para el equipo agregado.
                        $otdet = new OrdenTrabajoDetalle();
                        $otdet->setEquipo($eqNuevo);
                        $otdet->setEstadoOriginal($eqNuevo->getEstado());
                        $otdet->setEquipoUbicacionOriginal($eqNuevo->getUbicacionActual());
                        $otdet->setTipoRecambio('IN');
                        $otdet->setEntregado(true);
                        $ot->addDetalle($otdet);
                        $em->persist($ot);
                        $entity->addOrdenTrabajoDetalle($otdet);
                        $em->persist($entity);
                        // reubicar equipo nuevo
                        $eqNuevo->setEstado($estadoNuevo);
                        $eqNuevo->setNombre($descr_in[$key] . '_2');
                        $ubicNueva_actual = $eqNuevo->getUbicacionActual();
                        $ubicNueva_actual->setActual(0);
                        $ubicNueva_eqNuevo = new EquipoUbicacion();
                        $ubicNueva_eqNuevo->setFechaEntrega($entity->getFecha());
                        $ubicNueva_eqNuevo->setConceptoEntrega($concepto_entrega);
                        $ubicNueva_eqNuevo->setObservaciones($ubicNueva['observaciones']);
                        $departamento = $em->getRepository('ConfigBundle:Departamento')->find($ubicNueva['departamento']);
                        $ubicNueva_eqNuevo->setDepartamento($departamento);
                        $ubicNueva_eqNuevo->setEdificio($departamento->getEdificio());
                        $ubicNueva_eqNuevo->setUbicacion($departamento->getEdificio()->getUbicacion());
                        $piso = $em->getRepository('ConfigBundle:Piso')->find($ubicNueva['piso']);
                        $ubicNueva_eqNuevo->setPiso($piso);
                        $ubicNueva_eqNuevo->setActual(1);
                        $eqNuevo->addUbicacion($ubicNueva_eqNuevo);
                        $em->persist($eqNuevo);
                        $otdet->setEquipoUbicacionFinal($ubicNueva_eqNuevo);
                        $em->persist($otdet);
                        $em->flush();
                    }

                    // cambio de nombres
                    foreach ($eqid_out as $key => $eq) {
                        $equipo = $em->getRepository('AppBundle:Equipo')->find($eq);
                        $equipo->setNombre($descr_out[$key]);
                        $em->persist($equipo);
                    }
                    foreach ($eqid_in as $key => $new) {
                        $eqNuevo = $em->getRepository('AppBundle:Equipo')->find($new);
                        $eqNuevo->setNombre($descr_in[$key]);
                        $em->persist($eqNuevo);
                    }
                    $entity->setEquipoUbicacionFinal($ubicNueva_eqNuevo->getDepartamento());
                    $txtOut = 'Reubicado en: <strong>' . $ubic_nueva->getTexto() . '</strong> como <strong>' . $estado->getNombre() . '</strong>. ';
                    $txtIn = 'Reubicado en: <strong>' . $ubicNueva_eqNuevo->getTexto() . '</strong> como <strong>' . $estadoNuevo->getNombre() . '</strong>. ';
                    $entity->setDescripcion($entity->getDescripcion() . ' | ' . $txtOut . ' | ' . $txtIn);
                    $em->persist($entity);
                    $em->flush();
                    $salida = 'OK';
                }
                /* if( $entity->getTipoTarea()->getAbreviatura()=='EE' ){
                  // Entrega de equipos
                  $ids = $request->get('ids');
                  $estados = $request->get('estados');
                  $equipoubicacion = $request->get('appbundle_equipoubicacion');
                  $dpto = $em->getRepository('ConfigBundle:Departamento')->find($equipoubicacion['departamento']);
                  $piso = $em->getRepository('ConfigBundle:Piso')->find($equipoubicacion['piso']);
                  foreach ($ids as $key => $value) {
                  $est = $em->getRepository('ConfigBundle:Estado')->find($estados[$key]);
                  foreach ($entity->getOrdenTrabajoDetalles() as $det) {
                  if( $det->getId()==$value ){
                  $eq = $det->getEquipo();
                  $eq->setEstado($est);
                  $det->setEntregado(true);
                  $ubicOrignal = $eq->getUbicacionActual();
                  $ubicOrignal->setActual(0);
                  // nueva ubicacion
                  $ubicacion = new EquipoUbicacion();
                  $ubicacion->setActual(true);
                  $ubicacion->setDepartamento($dpto);
                  $ubicacion->setEdificio($dpto->getEdificio());
                  $ubicacion->setUbicacion($dpto->getEdificio()->getUbicacion());
                  $ubicacion->setPiso($piso);
                  $ubicacion->setFechaEntrega(new \DateTime());
                  $eq->addUbicacion($ubicacion);
                  $em->persist($eq);
                  $em->persist($det);
                  }
                  }
                  }
                  $entity->setDescripcion($ubicacion->getTexto());
                  $entity->setEquipoUbicacionFinal($dpto);
                  $em->flush();
                  if( isset($data['terminarSoporte']) ){
                  $ot->setEstado('CERRADO');
                  $em->persist($ot);
                  if( $ot->getRequerimiento()){
                  $req = $ot->getRequerimiento();
                  $cerrar = true;
                  foreach ($req->getTecnicosAsignados() as $otAsociada) {
                  if( $otAsociada->getEstado()!= 'CERRADO' ){
                  $cerrar = false;
                  }
                  }
                  if( $cerrar ){
                  $req->setEstado('FINALIZADO');
                  $em->persist($req);
                  }
                  }
                  $em->flush();
                  $msg = 'OUT';
                  }
                  $url = $this->generateUrl('print_entrega_equipo', array('id' => $entity->getId()));
                  } */
                $em->getConnection()->commit();
                $this->addFlash('success', 'Tarea registrada con éxito!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', 'Esta operación no pudo ser realizada! ' . $ex->getMessage());
                //UtilsController::errorMessage($ex->getErrorCode());
            }
        }
        else {
            $errmsj = json_encode(UtilsController::getErrorMessages($form), JSON_UNESCAPED_UNICODE);
            $this->addFlash('danger', 'Esta operación no pudo ser realizada! ' . $errmsj);
            return $this->redirectToRoute('soporte_ordentrabajo_tareas', array('id' => $ot->getId()));
        }

        //$result = array('msg'=>$msg,'url'=>$url);
        //return new Response( json_encode($result) );
        if ($salida == 'OK') {
            return $this->redirectToRoute('soporte_ordentrabajo_tareas', array('id' => $ot->getId()));
        }
        if ($salida == 'OUT') {
            return $this->redirectToRoute('soporte_ordentrabajo_edit', array('id' => $ot->getId()));
        }
    }

    /**
     * @Route("/{id}/renderRemoveEquipoAsociado", name="render-remove-equipo-asociado")
     * @Method("GET")
     */
    public function renderRemoveEquipoAsociadoAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $detOT = $em->getRepository('AppBundle:OrdenTrabajoDetalle')->find($id);
        $src = $request->get('src');
        $form = $this->createForm(new EstadoUbicacionType(), null, array(
            'action' => $this->generateUrl('remove-equipo-asociado'),
            'method' => 'GET',
        ));
        $form->get('src')->setData($src);
        $form->get('otDetalle')->setData($id);
        if ($detOT->getEstadoOriginal()) {
            $form->get('estado')->setData($detOT->getEstadoOriginal());
        }
        if ($detOT->getEquipoUbicacionOriginal()) {
            $ubic = $detOT->getEquipoUbicacionOriginal();
            $form->get('ubicacion')->get('ubicacion')->setData($ubic->getDepartamento()->getEdificio()->getUbicacion());
            $form->get('ubicacion')->get('edificio')->setData($ubic->getDepartamento()->getEdificio());
            $form->get('ubicacion')->get('departamento')->setData($ubic->getDepartamento());
            $form->get('ubicacion')->get('piso')->setData($ubic->getPiso());
            $form->get('ubicacion')->get('conceptoEntrega')->setData($ubic->getConceptoEntrega());
        }
        else {
            $ubic = $detOT->getOrdenTrabajo()->getRequerimiento()->getSolicitante();
            $form->get('ubicacion')->get('ubicacion')->setData($ubic->getEdificio()->getUbicacion());
            $form->get('ubicacion')->get('edificio')->setData($ubic->getEdificio());
            $form->get('ubicacion')->get('departamento')->setData($ubic);
            $form->get('ubicacion')->get('piso')->setData($ubic->getPisos()[0]);
            //$form->get('ubicacion')->get('conceptoEntrega')->setData( $ubic->getConceptoEntrega() );
        }

        $html = $this->renderView('AppBundle:OrdenTrabajo:partial-remove-equipo.html.twig',
                array('detOT' => $detOT, 'form' => $form->createView())
        );
        return new Response($html);
    }

    /**
     * @Route("/removeEquipoAsociado", name="remove-equipo-asociado")
     * @Method("GET")
     */
    public function removeEquipoAsociadoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('appbundle_estadoubicacion');
        if ($form) {
            $otDet = $em->getRepository('AppBundle:OrdenTrabajoDetalle')->find($form['otDetalle']);
            $src = $form['src'];
        }
        else {
            $id = $request->get('id');
            $otDet = $em->getRepository('AppBundle:OrdenTrabajoDetalle')->find($id);
            $src = $request->get('src');
        }
        if (!$otDet) {
            throw $this->createNotFoundException('No se encuentra.');
        }
        $ot = $otDet->getOrdenTrabajo();
        $reqId = $ot->getRequerimiento()->getId();
        // verificar a donde volver

        if ($src == 'OT') {
            // volver a edicion de requerimiento solapa de la OT
            $session = $this->get('session');
            $session->set('otid', $ot->getId());
            $route = $this->generateUrl('soporte_requerimiento_edit', array('id' => $reqId));
        }
        else {
            // volver a tareas
            $route = $this->generateUrl('soporte_ordentrabajo_tareas', array('id' => $ot->getId()));
        }
        try {
            $em->getConnection()->beginTransaction();
            $reqdet = $otDet->getRequerimientoDetalle();
            $equipo = $otDet->getEquipo();
            $ubic_nueva = new EquipoUbicacion();
            $ubic_nueva->setFechaEntrega(new \DateTime());
            if ($form) {
                // No hay ubicacion original - se carga del modal
                $concepto_entrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->find($form['ubicacion']['conceptoEntrega']);
                $estado = $em->getRepository('ConfigBundle:Estado')->find($form['estado']);
                $equipo->setEstado($estado);
                $departamento = $em->getRepository('ConfigBundle:Departamento')->find($form['ubicacion']['departamento']);
                $piso = $em->getRepository('ConfigBundle:Piso')->find($form['ubicacion']['piso']);
            }
            else {
                if ($otDet->getEstadoOriginal()) {
                    $equipo->setEstado($otDet->getEstadoOriginal());
                }
                if ($otDet->getEquipoUbicacionOrdentrabajo()) {
                    // volver al original y eliminar el creado al agregar
                    $otDet->getEquipoUbicacionOriginal()->setActual(1);
                    $em->persist($otDet);
                    $ubic_nueva = null;
                }
                else {
                    // crear ubicación nueva desde la original
                    $ubicOriginal = $otDet->getEquipoUbicacionOriginal();
                    $concepto_entrega = $ubicOriginal->getConceptoEntrega();
                    $departamento = $ubicOriginal->getDepartamento();
                    $piso = $ubicOriginal->getPiso();
                    $ubic_nueva->setObservaciones($ubicOriginal->getObservaciones());
                    $ubic_nueva->setRedIp($ubicOriginal->getRedIp());
                }
            }
            if ($ubic_nueva) {
                RequerimientoController::unsetUbicaciones($em, $equipo->getId());
                // crear ubicacion nueva
                $ubic_nueva->setDepartamento($departamento);
                $ubic_nueva->setEdificio($departamento->getEdificio());
                $ubic_nueva->setUbicacion($departamento->getEdificio()->getUbicacion());
                $ubic_nueva->setPiso($piso);
                $ubic_nueva->setConceptoEntrega($concepto_entrega);
                $ubic_nueva->setActual(1);
                $equipo->addUbicacion($ubic_nueva);
            }
            $em->persist($equipo);
            $em->remove($otDet);
            $em->flush();
            if ($reqdet) {
                $em->remove($reqdet);
                $em->flush();
            }
            $em->getConnection()->commit();
            $this->addFlash('success', 'Se ha quitado el equipo de la OT! ');
        }
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->addFlash('danger', 'Esta operación no pudo ser realizada! ' . $ex->getMessage());
        }
        return $this->redirect($route);
    }

    /**
     * @Route("/{id}/pdfEntregaEquipo",
     * name="print_entrega_equipo")
     * @Method("GET")
     */
    public function pdfEntregaEquipoAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        //  $id = $request->get('id');
        $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);

        if (!$ot->getReubicacionEquipo()) {
            $this->addFlash('danger', 'No se han realizado reubicaciones de equipo.');
            return $this->redirectToRoute('soporte_ordentrabajo_tareas', array('id' => $ot->getId()));
        }
        /* foreach ($ot->getTareas() as $tarea) {

          } */
        //   $fecha = UtilsController::longDateSpanish( $tarea->getFecha() );
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';


        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:OrdenTrabajo:acta-entrega-recepcion.pdf.twig',
                array('tareas' => $ot->getTareasReubicacionEquipo(), 'logo' => $logo1), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=acta-entrega-recepcion.pdf'));
    }

    /**
     * @Route("/{id}/pdfReemplazoEquipo",
     * name="print_reemplazo_equipo")
     * @Method("GET")
     */
    public function pdfReemplazoEquipoAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        //  $id = $request->get('id');
        $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($id);

        if (!$ot->getReemplazoEquipo()) {
            $this->addFlash('danger', 'No se han realizado reemplazos de equipo.');
            return $this->redirectToRoute('soporte_ordentrabajo_tareas', array('id' => $ot->getId()));
        }

        $tareas = $em->getRepository('AppBundle:OrdenTrabajo')->findTareasReemplazoEquipoPdf($id);

        //   $fecha = UtilsController::longDateSpanish( $tarea->getFecha() );
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';


        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:OrdenTrabajo:acta-reemplazo-equipo.pdf.twig',
                array('tareas' => $tareas, 'logo' => $logo1), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=acta-reemplazo-equipos.pdf'));
    }

    /**
     * DOCUMENTACION ANEXA A LA OT
     */

    /**
     * @Route("/renderAddTareaDocumantacion/{ot}", name="render-add-tarea-documentacion")
     * @Method("GET")
     */
    public function renderAddTareaDocumantacionAction($ot) {
        $doc = new Documentacion();
        $form = $this->createForm(new DocumentacionType(), $doc, array(
            'action' => $this->generateUrl('add-tarea-documentacion', array('otid' => $ot)),
            'method' => 'POST',
        ));

        $html = $this->renderView('AppBundle:OrdenTrabajo:prototype-tarea-documento.html.twig',
                array('doc' => $doc, 'form' => $form->createView()));
        return new Response($html);
    }

    /**
     * @Route("/addTareaDocumantacion/{otid}", name="add-tarea-documentacion")
     * @Method("POST")
     */
    public function addTareaDocumantacionAction(Request $request, $otid) {
        $doc = new Documentacion();
        $form = $this->createForm(new DocumentacionType(), $doc, array(
            'action' => $this->generateUrl('add-tarea-documentacion', array('otid' => $otid)),
            'method' => 'POST',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $ot = $em->getRepository('AppBundle:OrdenTrabajo')->find($otid);
            $em->getConnection()->beginTransaction();
            $em->getConnection()->setAutoCommit(false);
            try {
                if (!$this->getUser()) {
                    return new Response(json_encode('Debe iniciar sesión'));
                }
                $doc->setOrdenTrabajo($ot);
                $em->persist($doc);
                $em->flush();
                $em->getConnection()->commit();
                $html = $this->renderView('AppBundle:OrdenTrabajo:show-item-documento.html.twig',
                        array('doc' => $doc));
                $result = array('msg' => 'OK', 'html' => $html);
                return new Response(json_encode($result));
            }
            catch (Exception $ex) {
                $em->getConnection()->rollback();
                return new Response($ex->getMessage());
            }
        }
        return new Response('ERROR');
    }

    /**
     * @Route("/tareaDocumantacionDelete/{id}", name="del-tarea-documentacion")
     * @Method("DELETE")
     */
    public function tareaDocumantacionDeleteAction($id) {
        //UtilsController::haveAccess($this->getUser(), 'ordentrabajo_delete');
        $em = $this->getDoctrine()->getManager();
        $doc = $em->getRepository('AppBundle:Documentacion')->find($id);
        if (!$doc) {
            throw $this->createNotFoundException('No existe la documentación.');
        }
        try {
            $em->getConnection()->beginTransaction();
            $em->remove($doc);
            $em->flush();
            $em->getConnection()->commit();
            return new Response('OK');
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            return new Response('ERROR');
        }
    }

}