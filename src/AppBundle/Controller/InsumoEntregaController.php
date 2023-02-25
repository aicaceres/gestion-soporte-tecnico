<?php

namespace AppBundle\Controller;
use AppBundle\Entity\InsumoEntrega;
use AppBundle\Entity\StockHistorico;
use AppBundle\Form\InsumoEntregaType;
use ConfigBundle\Controller\UtilsController;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Route("/entrega")
 */
class InsumoEntregaController extends Controller {

    /**
     * @Route("/", name="insumo_entrega")
     * @Method("GET")
     * @Template("AppBundle:InsumoEntrega:index.html.twig")
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_entrega');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_entrega');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'idUbicacion' => $request->get('idUbicacion'),
                    'idEdificio' => $request->get('idEdificio'),
                    'idDepartamento' => $request->get('idDepartamento'),
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
                        'estado' => $sessionFiltro['estado'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('idDepartamento' => 0, 'idEdificio' => 0, 'idUbicacion' => 0, 'estado' => '0',
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_entrega', $filtro);
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
        $entities = $em->getRepository('AppBundle:InsumoEntrega')->findEntregaByCriteria($filtro, $userId);
        return array(
            'entities' => $entities,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'filtro' => $filtro,
        );
    }

    /**
     * @Route("/new", name="insumo_entrega_new")
     * @Method("GET")
     * @Template("AppBundle:InsumoEntrega:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), 'insumo_entrega');
        $em = $this->getDoctrine()->getManager();
        $entity = new InsumoEntrega();
        $entity->setFecha(new DateTime());
        $entity->setEstado('ENTREGADO');
        $form = $this->createNewForm($entity);
        $form->get('hora')->setData($entity->getFecha()->format('H:i'));
        $userId = $this->getUser()->getId();
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);
        $depositosAsignados = $em->getRepository('ConfigBundle:Departamento')->findOneByDepositoEntrega(1);
        if (!$depositosAsignados) {
            $this->get('session')->getFlashBag()->add('error', 'No posee depósitos asignados para entrega de insumos!');
            return $this->redirectToRoute('insumo_entrega');
        }
        return array(
            'entity' => $entity,
            'ubicaciones' => $ubicaciones,
            'form' => $form->createView(),
        );
    }

    /**
     * @param InsumoEntrega $entity The entity
     * @return Form The form
     */
    private function createNewForm(InsumoEntrega $entity) {
        $form = $this->createForm(new InsumoEntregaType(), $entity, array(
            'action' => $this->generateUrl('insumo_entrega_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/", name="insumo_entrega_create")
     * @Method("PUT")
     * @Template("AppBundle:InsumoEntrega:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_entrega');
        $data = $request->get('appbundle_insumoentrega');
        $entity = new InsumoEntrega();
        $form = $this->createNewForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $em->getConnection()->setAutoCommit(false);
                $entity->setEstado('ENTREGADO');
                $entity->setFecha(new \DateTime(UtilsController::toAnsiDate($data['fecha']) . ' ' . $data['hora'] . ':00'));
                $em->persist($entity);
                $em->flush();

                if (count($entity->getDetalles()) > 0) {
                    $this->procesarEntrega($entity, $em);
                }

                $em->getConnection()->commit();
                $this->addFlash('success', 'La entrega fue registrada correctamente. ');
                return $this->redirectToRoute('insumo_entrega');
            }
            catch (Exception $ex) {
                $this->addFlash('danger', $ex->getMessage());
                $em->getConnection()->rollback();
                //$this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($this->getUser()->getId());
        return array(
            'entity' => $entity,
            'ubicaciones' => $ubicaciones,
            'form' => $form->createView(),
        );
    }

    private function procesarEntrega($entity, $em) {
        $deposito = $em->getRepository('ConfigBundle:Departamento')->find($entity->getDeposito()->getId());
        foreach ($entity->getDetalles() as $detalle) {
            //procesar items
            $insumo = $detalle->getInsumo();
            $cantidad = $detalle->getCantidad();
            $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($insumo->getId(), $deposito->getId());
            if ($stock) {
                $saldo = $stock->getCantidad() - $cantidad;
                // controlar que no quede en negativo el stock
                if ($saldo < 0) {
                    // no hay stock.
                    $em->getConnection()->rollback();
                    throw $this->createNotFoundException('No hay stock suficiente para cumplir este pedido.');
                }
                else {
                    $stock->setCantidad($stock->getCantidad() - $cantidad);
                    $em->persist($stock);
                    // Cargar movimiento
                    $movim = new StockHistorico();
                    $movim->setFecha(new \DateTime());
                    $movim->setTipo('ENTREGAINSUMO');
                    $movim->setSigno('-');
                    $movim->setMovimiento($entity->getId());
                    $movim->setInsumo($insumo);
                    $movim->setStock($insumo->getStockTotal());
                    $movim->setCantidad($cantidad);
                    $movim->setDeposito($deposito);
                    $em->persist($movim);
                    $em->flush();
                }
            }
        }
    }

    /**
     * @Route("/{id}/edit", name="insumo_entrega_edit")
     * @Method("GET")
     * @Template("AppBundle:InsumoEntrega:edit.html.twig")
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_entrega');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:InsumoEntrega')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la entrega.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        $editForm->get('hora')->setData($entity->getFecha()->format('H:i'));
        $userId = $this->getUser()->getId();
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);
        $depositosAsignados = $em->getRepository('ConfigBundle:Departamento')->findOneByDepositoEntrega(1);
        if (!$depositosAsignados) {
            $this->get('session')->getFlashBag()->add('error', 'No posee depósitos asignados para entrega de insumos!');
            return $this->redirectToRoute('insumo_entrega');
        }

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'ubicaciones' => $ubicaciones
                //'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * @param InsumoEntrega $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(InsumoEntrega $entity) {
        $form = $this->createForm(new InsumoEntregaType(), $entity, array(
            'action' => $this->generateUrl('insumo_entrega_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="insumo_entrega_update")
     * @Method("PUT")
     * @Template("AppBundle:InsumoEntrega:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:InsumoEntrega')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la entrega.');
        }
        $insumoxTareas = new ArrayCollection();
        foreach ($entity->getDetalles() as $item) {
            // guardo los insumosxtareas para actualizar segun entrega
            if ($item->getInsumoxTarea())
                $insumoxTareas->add($item);
        }

        $data = $request->get('appbundle_insumoentrega');
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            $em->getConnection()->setAutoCommit(false);
            try {
                $entity->setFecha(new \DateTime(UtilsController::toAnsiDate($data['fecha']) . ' ' . $data['hora'] . ':00'));
                $em->persist($entity);
                $em->flush();
                // aprobar y registrar salida
                if ($entity->getEstado() == 'PENDIENTE') {
                    $this->procesarEntrega($entity, $em);
                    $entity->setEstado('ENTREGADO');

                    if (count($insumoxTareas) > 0) {
                        // procesar insumoxtarea
                        $this->procesarInsumoxTareas($entity, $insumoxTareas, $em);
                    }
                }

                $em->getConnection()->commit();
                return $this->redirectToRoute('insumo_entrega');
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

    private function procesarInsumoxTareas($entity, $insumoxTareas, $em) {
        foreach ($entity->getDetalles() as $detalle) {
            $exist = $insumoxTareas->contains($detalle);
            if ($exist) {
                // update and remove
                $tarea = $detalle->getInsumoxTarea();
                $tarea->setCantidadAprobada($detalle->getCantidad());
                $tarea->setFechaAutorizado(new \DateTime());
                $tarea->setAutorizante($this->getUser());
                $em->persist($tarea);
                $insumoxTareas->removeElement($detalle);
            }
        }
        if (count($insumoxTareas) > 0) {
            //procesar los rechazados
            foreach ($insumoxTareas as $item) {
                $itemTarea = $item->getInsumoxTarea();
                $itemTarea->setCantidadAprobada(0);
                $itemTarea->setFechaAutorizado(new \DateTime());
                $itemTarea->setAutorizante($this->getUser());
                $em->persist($itemTarea);
            }
        }
        $em->flush();
    }

    /**
     * @Route("/{id}/modalshow", name="modal_insumo_entrega_show")
     * @Method("GET")
     * @Template()
     */
    public function modalShowAction($id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_entrega');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository('AppBundle:InsumoEntrega')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro de entrega.');
        }
        $html = $this->renderView('AppBundle:InsumoEntrega:modalShow.html.twig',
                array('entity' => $entity));
        return new Response($html);
    }

    /**
     * @Route("{id}/pdfRemito.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_insumo_entrega_remito")
     * @Method("GET")
     */
    public function pdfRemitoAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entrega = $em->getRepository('AppBundle:InsumoEntrega')->find($id);
        $entregaNro = str_pad($entrega->getId(), 6, '0', STR_PAD_LEFT);
        $fecha = UtilsController::longDateSpanish($entrega->getFecha());
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:InsumoEntrega:remito.pdf.twig',
                array('entrega' => $entrega, 'logo' => $logo1, 'fecha' => $fecha), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=insumo_entrega_remito_' . $entregaNro . '.pdf'));
    }

}