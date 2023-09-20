<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Insumo;
use AppBundle\Form\InsumoType;
//use AppBundle\Entity\Stock;
use AppBundle\Entity\StockHistorico;
use AppBundle\Entity\Mensajeria;
use AppBundle\Entity\Tarea;
use AppBundle\Entity\InsumoxTarea;
use AppBundle\Controller\StockController;

/**
 * @Route("/insumo")
 */
class InsumoController extends Controller {

    /**
     * @Route("/renderSearchInsumo", name="render_search_insumo")
     * @Method("GET")
     */
    public function renderSearchInsumoAction() {

        $partial = $this->renderView('AppBundle:Insumo:partial-search-insumo.html.twig');
        return new Response($partial);
    }

    /**
     * @Route("/insumoSearchDatatables", name="insumo_search_datatables")
     * @Method("POST")
     * @Template()
     */
    public function insumoSearchDatatablesAction(Request $request) {
        // Set up required variables
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->repository = $this->entityManager->getRepository('AppBundle:Insumo');
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->request->get('draw'));
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');
        }
        else // If the request is not a POST one, die hard
            die;

        // Orders
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = "array or whatever is needed";
        $subclase = $request->request->get('subclase');
        $deposito = $request->request->get('deposito');
        // Get results from the Repository
        $results = $this->repository->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions = null, $subclase, $deposito);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $this->repository->count();
        // Get total number of results
        $selected_objects_count = count($objects);
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        // Construct response
        $response = '{
            "draw": ' . $draw . ',
            "recordsTotal": ' . $total_objects_count . ',
            "recordsFiltered": ' . $filtered_objects_count . ',
            "data": [';

        $i = 0;

        foreach ($objects as $key => $insumo) {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column) {
                // In all cases where something does not exist or went wrong, return -
                $responseTemp = "-";

                switch ($column['name']) {
                    case 'tipo': {
                            $tipo = $insumo->getTipo();
                            // This cannot happen if inner join is used
                            // However it can happen if left or right joins are used
                            if ($tipo !== null) {
                                $nameTipo = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $tipo->getNombre()));
                                $responseTemp = $nameTipo;
                            }
                            break;
                        }
                    case 'barcode': {
                            // Do this kind of treatments if you suspect that the string is not JS compatible
                            $name = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $insumo->getBarcode()));
                            $responseTemp = $name;

                            break;
                        }
                    case 'marca': {
                            $marca = $insumo->getMarca();
                            if ($marca !== null) {
                                $nameMarca = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $marca->getNombre()));
                                $responseTemp = $nameMarca;
                            }
                            break;
                        }
                    case 'modelo': {
                            $modelo = $insumo->getModelo();
                            if ($modelo !== null) {
                                $nameModelo = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $modelo->getNombre()));
                                $responseTemp = $nameModelo;
                            }
                            break;
                        }
                    case 'stock': {
                            $responseTemp = round($insumo->getStockTotal());
                            break;
                        }
                    case 'checks': {
                            $responseTemp = "<input class='ckItem' type='checkbox' data-id='" . $insumo->getId() . "' data-barcode='" . $insumo->getBarcode() . "' value='" . $insumo->getId() . "' />";
                            break;
                        }
                }

                // Add the found data to the json
                $response .= $responseTemp;

                if (++$j !== $nbColumn)
                    $response .= '","';
            }

            $response .= '"]';

            // Not on the last item
            if (++$i !== $selected_objects_count)
                $response .= ',';
        }

        $response .= ']}';

        // Send all this stuff back to DataTables
        return new Response($response);
    }

    /**
     * @Route("/getAutocompleteInsumoTipo", name="get-autocomplete-insumo-tipo")
     * @Method("GET")
     */
    public function getAutocompleteInsumoTipo(Request $request) {
        $term = $request->get('search');
        $subclase = $request->get('subclase');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Tipo')->filterTipoInsumoByTerm($term, $subclase);
        return new JsonResponse($results);
    }

    /**
     * @Route("/getAutocompleteInsumoMarca", name="get-autocomplete-insumo-marca")
     * @Method("GET")
     */
    public function getAutocompleteInsumoMarca(Request $request) {
        $term = $request->get('q');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Marca')->filterMarcaInsumoByTerm($term);
        return new JsonResponse($results);
    }

    /**
     * @Route("/getAutocompleteInsumoModelo", name="get-autocomplete-insumo-modelo")
     * @Method("GET")
     */
    public function getAutocompleteInsumoModelo(Request $request) {
        $term = $request->get('q');
        $marca = $request->get('marca');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Modelo')->filterModeloInsumoByTerm($term, $marca);
        return new JsonResponse($results);
    }

    /**
     * @Route("/getCompraInsumos", name="get-compra-insumos")
     * @Method("GET")
     */
    public function getCompraInsumos(Request $request) {
        $term = explode(' ', $request->get('q'));
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('AppBundle:Insumo')->filterByTerm($term);
        return new JsonResponse($results);
    }

    /**
     * @Route("/findDataInsumo", name="find_data_insumo")
     * @Method("GET")
     */
    public function findDataInsumoAction(Request $request) {
        $item = $request->get('item');
        $dep = $request->get('dep');
        $em = $this->getDoctrine()->getManager();
        $insumo = $em->getRepository('AppBundle:Insumo')->find($item);
        return new Response(json_encode(array('id' => $insumo->getId(), 'codigo' => $insumo->getCodigoItem(),
                'nombre' => $insumo->getTexto(), 'stock' => $insumo->getStockByDeposito($dep))));
    }

    /**
     * @Route("/findInsumoDataById", name="find_insumo_data_by_id")
     * @Method("GET")
     */
    public function findInsumoDataById(Request $request) {
        // Busca equipo para asociar a requerimiento
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $insumo = $em->getRepository('AppBundle:Insumo')->find($id);
        $data = json_encode(array('msj' => 'OK', 'id' => $insumo->getId(), 'nombre' => $insumo->getTexto(), 'barcode' => $insumo->getBarcode()));
        return new Response(($insumo) ? $data : null);
    }

    /**
     * @Route("/", name="insumo_adm")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        UtilsController::haveAccess($this->getUser(), 'insumo');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entities = $em->getRepository('AppBundle:Insumo')->findAll();
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }
        return array(
            'entities' => $entities,
            'deleteForms' => $deleteForms
        );
    }

    /**
     * @Route("/", name="insumo_create")
     * @Method("PUT")
     * @Template("AppBundle:Insumo:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_new');
        $data = $request->get('appbundle_insumo');
        $new = $data['savenew'];
        $entity = new Insumo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($entity);
                $em->flush();
                if ($new == 'S') {
                    $this->addFlash('success', 'El insumo fue creado. Puede crear el siguiente!');
                    return $this->redirectToRoute('insumo_adm_new');
                }
                else {
                    $this->addFlash('success', 'El insumo fue creado!');
                    return $this->redirectToRoute('insumo_adm');
                }
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @param Insumo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Insumo $entity) {
        $form = $this->createForm(new InsumoType(), $entity, array(
            'action' => $this->generateUrl('insumo_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="insumo_adm_new")
     * @Method("GET")
     * @Template("AppBundle:Insumo:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), 'insumo_new');
        $entity = new Insumo();
        $form = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="insumo_adm_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_edit');
        $em = $this->getDoctrine()->getManager();
        //if ($this->getUser()->getRol()->getAdmin()) {
        $em->getFilters()->disable('softdeleteable');
        //}
        $entity = $em->getRepository('AppBundle:Insumo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el insumo.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        $depositos = $em->getRepository('ConfigBundle:Departamento')->findByDeposito(1);
        //$repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
        //$item = $em->find('AppBundle\Entity\Insumo', $id /*article id*/);
        //$logs = $repo->getLogEntries($item);
        StockController::setearDatosHistorico($entity->getStockHistorico(), $em, $this);
        /* foreach( $entity->getStockHistorico() as $historico){
          $comp = $em->getRepository($historico->getEntidadMovimiento())->find( $historico->getMovimiento() );
          switch ($historico->getTipo()) {
          case 'AJUSTE':
          $historico->nroComprobante = 'AJ '. str_pad($comp->getId(), 6, '0', STR_PAD_LEFT);
          $historico->urlMovimiento = $this->generateUrl('modal_insumo_ajuste_show',array('id'=>$comp->getId()));
          break;
          case 'COMPRA':
          $historico->nroComprobante = 'OC '. $comp->getNroOc();
          $historico->urlMovimiento = $this->generateUrl('modal_compra_admin_show',array('id'=>$comp->getId()));
          break;
          case 'MOVIMIENTO':
          $historico->nroComprobante = 'MI '. str_pad($comp->getId(), 6, '0', STR_PAD_LEFT);
          $historico->urlMovimiento = $this->generateUrl('modal_insumo_movimiento_show',array('id'=>$comp->getId()));
          break;
          case 'SOPORTE':
          $historico->equipo = $em->getRepository('AppBundle:Equipo')->find( $comp->getTarea()->getOrdenTrabajoDetalles()[0]->getEquipo()->getId() );
          $historico->nroComprobante = 'OT '.$comp->getTarea()->getOrdenTrabajo()->getNroOT();
          $historico->urlMovimiento = $this->generateUrl('soporte_ordentrabajo_show',array('id'=>$comp->getTarea()->getOrdenTrabajo()->getId()));
          break;
          default:
          return NULL;
          }
          } */

        return array(
            'entity' => $entity,
            'depositos' => $depositos,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * @param Insumo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Insumo $entity) {
        $form = $this->createForm(new InsumoType(), $entity, array(
            'action' => $this->generateUrl('insumo_adm_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="insumo_adm_update")
     * @Method("PUT")
     * @Template("AppBundle:Insumo:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_edit');
        $em = $this->getDoctrine()->getManager();
        //if ($this->getUser()->getRol()->getAdmin()) {
        $em->getFilters()->disable('softdeleteable');
        //}
        $entity = $em->getRepository('AppBundle:Insumo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el insumo.');
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            try {
                $em->flush();
                return $this->redirectToRoute('insumo_adm');
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
        );
    }

    /**
     * @Route("/delete/{id}", name="insumo_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_delete');
        $em = $this->getDoctrine()->getManager();
        //if ($this->getUser()->getRol()->getAdmin()) {
        $em->getFilters()->disable('softdeleteable');
        //}
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity = $em->getRepository('AppBundle:Insumo')->find($id);
                if (!$entity) {
                    throw $this->createNotFoundException('No existe el insumo.');
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
                $this->addFlash('success', 'El insumo fue eliminado!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }

        return $this->redirectToRoute('insumo_adm');
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('insumo_delete', array('id' => $id)))
                ->setMethod('DELETE')
                ->getForm()
        ;
    }

    /**
     * @Route("/movimiento", name="insumo_movimiento")
     * @Method("GET")
     * @Template()
     */
    public function movimientoAction() {
        UtilsController::haveAccess($this->getUser(), 'insumo_movimiento');
        $em = $this->getDoctrine()->getManager();
        //if ($this->getUser()->getRol()->getAdmin()) {
        $em->getFilters()->disable('softdeleteable');
        //}
        $entities = $em->getRepository('AppBundle:Insumo')->findAll();
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }
        return array(
            'entities' => $entities,
            'deleteForms' => $deleteForms
        );
    }

    /**
     * SOLICITUDES DE INSUMO DESDE SOPORTE
     */

    /**
     * @Route("/solicitud", name="insumo_solicitud")
     * @Method("GET")
     * @Template("AppBundle:Insumo:solicitud.html.twig")
     */
    public function solicitudAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_solicitud');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $estado = ( is_null($request->get('estado')) ) ? 1 : $request->get('estado');
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $entities = $em->getRepository('AppBundle:Insumo')->findSolicitudes($periodo, $estado);

        //$depositos = $em->getRepository('ConfigBundle:Departamento')->findByDeposito(1);
        $depositos = $em->getRepository('ConfigBundle:Departamento')->findBy(array('deposito' => 1, 'depositoEntrega' => 0), array('inicial' => 'desc'));
        $depId = $request->get('depId');
        if (count($depositos) > 0) {
            if (!$depId) {
                $depId = $depositos[0]->getId();
            }
        }
        else {
            $this->addFlash('error', 'No hay depósitos');
        }
        return array(
            'entities' => $entities,
            'desde' => $periodo['desde'],
            'hasta' => $periodo['hasta'],
            'estado' => $estado,
            'depId' => $depId,
            'depositos' => $depositos
        );
    }

    /**
     * @Route("/renderAprobarSolicitud", name="render_aprobar_solicitud")
     * @Method("GET")
     */
    public function renderAprobarSolicitudAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $dep = $request->get('dep');
        $entity = $em->getRepository('AppBundle:InsumoxTarea')->find($id);
        $partial = $this->renderView('AppBundle:Insumo:partial-aprobar-solicitud.html.twig', array('entity' => $entity, 'deposito' => $dep));
        return new Response($partial);
    }

    /**
     * @Route("/updateSolicitud", name="update_solicitud")
     * @Method("POST")
     */
    public function updateSolicitudAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $tipo = $request->get('tipo');
        $cant = number_format($request->get('cantidad'), 2, '.', '');
        $insumoId = $request->get('insumo');
        $depositoId = $request->get('dep');
        $entity = $em->getRepository('AppBundle:InsumoxTarea')->find($id);
        if (!is_null($entity->getCantidadAprobada())) {
            return new Response(json_encode('DONE'));
        }
        $newTarea = new Tarea();
        $newTarea->setFecha(new \DateTime());
        $tipoRespuesta = $em->getRepository('ConfigBundle:TipoTarea')->findOneByAbreviatura('RI');
        $newTarea->setTipoTarea($tipoRespuesta);
        $newTarea->setOrdenTrabajo($entity->getTarea()->getOrdenTrabajo());
        $insumo = null;
        // crear nueva tarea y clonar el insumoxtarea
        try {
            $em->getConnection()->beginTransaction();
            $em->getConnection()->setAutoCommit(false);
            $entity->setFechaAutorizado(new \DateTime());
            $entity->setAutorizante($this->getUser());
            $aprobado = false;
            switch ($tipo) {
                case 'APROBAR':
                    $entity->setCantidadAprobada($entity->getCantidad());
                    $insumo = $entity->getInsumo();
                    // registrar aprobado y poner mensaje a tarea nueva
                    $aprobado = true;
                    break;
                case 'RECHAZAR':
                    $entity->setCantidadAprobada(0);
                    $newTarea->setDescripcion('<strong>RECHAZADO</strong>: ' . $entity->getCantidad() . ' - ' . $entity->getDescripcion());
                    // registrar rechazado y poner mensaje a tarea nueva
                    //$insumo = ($entity->getInsumo()) ? $entity->getInsumo() : null;
                    break;
                default:
                    $insumo = $em->getRepository('AppBundle:Insumo')->find($insumoId);
                    $entity->setInsumo($insumo);
                    $entity->setCantidadAprobada($cant);
                    $aprobado = $entity->getCantidadAprobada() > 0;
                    // registrar aprobado y poner mensaje a tarea nueva
                    break;
            }
            $em->persist($entity);
            $em->flush();
            // completar registro de tarea nueva
            //$cloneInsumo = clone($entity);
            // $newTarea->addInsumo($cloneInsumo);
            foreach ($entity->getTarea()->getOrdenTrabajoDetalles() as $otdetalle) {
                $newTarea->addOrdenTrabajoDetalle($otdetalle);
            }

            if ($aprobado) {
                //$cloneInsumo->setInsumo($insumo);
                // actualizar stock
                $deposito = $em->getRepository('ConfigBundle:Departamento')->find($depositoId);
                $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($entity->getInsumo()->getId(), $deposito->getId());

                if ($stock) {
                    $saldo = $stock->getCantidad() - $entity->getCantidadAprobada();
                    // controlar que no quede en negativo el stock
                    if ($saldo < 0) {
                        // no hay stock.
                        $em->getConnection()->rollback();
                        return new Response(json_encode('SINSTOCK'));
                    }
                    else {
                        $stock->setCantidad($stock->getCantidad() - $entity->getCantidadAprobada());
                        $em->persist($stock);
                        // Cargar movimiento
                        $movim = new StockHistorico();
                        $movim->setFecha(new \DateTime());
                        $movim->setTipo('SOPORTE');
                        $movim->setSigno('-');
                        $movim->setMovimiento($entity->getId());
                        $movim->setInsumo($insumo);
                        $movim->setStock($insumo->getStockTotal());
                        $movim->setCantidad($entity->getCantidadAprobada());
                        $movim->setDeposito($deposito);
                        $em->persist($movim);
                        $em->flush();
                    }
                }
            }
            // generar mensajería
            $textoMensaje = 'Solicitud del ' . $entity->getCreated()->format('d/m/Y') .
                ' por "' . $entity->getCantidad() . '" de "';
            if ($insumo)
                $insumoTxt = $insumo->getTexto();
            else
                $insumoTxt = $entity->getDescripcion();
            $textoMensaje = $textoMensaje . $insumoTxt . '"';
            $asunto = 'Insumo Aprobado';
            switch ($entity->getEstado()) {
                case 'R':
                    $span = '<span style="font-weight:normal;" class="badge bg-red">Rechazado</span>';
                    $respuesta = ' se ha rechazado.';
                    $asunto = 'Insumo Rechazado';
                    break;
                case 'AP':
                    $span = '<span style="font-weight:normal;" class="badge bg-orange">Aprobación Parcial</span>';
                    $respuesta = ' se ha aprobado parcialmente.';
                    $newTarea->setDescripcion('<strong>APROBADO</strong> ' . $entity->getCantidadAprobada() . ' de ' . $entity->getCantidad() . ' - ' . $insumoTxt);
                    break;
                case 'AT':
                    $span = '<span style="font-weight:normal;" class="badge bg-green">Aprobado</span>';
                    $respuesta = ' se ha aprobado en su totalidad.';
                    $newTarea->setDescripcion('<strong>APROBADO</strong> ' . $entity->getCantidad() . ' - ' . $insumoTxt);
                    break;
            }
            $textoMensaje = $textoMensaje . $respuesta;
            $mensaje = new Mensajeria();
            $mensaje->setDestinatario($entity->getCreatedBy());
            $mensaje->setAsunto($asunto);
            $mensaje->setMensaje($textoMensaje);
            $em->persist($mensaje);
            $em->persist($newTarea);

            $em->flush();

            $data = array(
                'span' => $span,
                'nombre' => ($insumo) ? $insumo->getTexto() : null,
                'stock' => ($insumo) ? $insumo->getStockByDeposito($deposito->getId()) : null);
            $jsondata = json_encode($data);
            $em->getConnection()->commit();
            return new Response($jsondata);
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            //var_dump($ex->getMessage());
            return new Response(json_encode('ERROR'));
        }
    }

    /**
     * @Route("/getSolInsumoPendientes", name="get_solinsumo_pendientes")
     * @Method("GET")
     */
    public function getSolInsumoPendientesAction() {
        $em = $this->getDoctrine()->getManager();
        $solicitudes = $em->getRepository('AppBundle:Insumo')->findSolicitudesPendientes();
        $entregas = $em->getRepository('AppBundle:Insumo')->findEntregasPendientes();
        return new JsonResponse(array('sol' => $solicitudes, 'ent' => $entregas));
    }

    /**
     * @Route("/getInsumoLogs", name="get_insumo_logs")
     * @Method("GET")
     * @Template()
     */
    public function getInsumoLogs(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'view_logs');
        $id = $request->get('id');
        $entity = "AppBundle\\Entity\\Insumo";
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        if ($id) {
            // ver de una sola entidad
            $item = $em->find($entity, $id);
            $logs = $repo->getLogEntries($item);
        }
        else {
            // ver de toda la tabla
            $logs = $em->getRepository('Gedmo\Loggable\Entity\LogEntry')->findByObjectClass($entity);
        }
        $items = array();
        foreach ($logs as $log) {

            if ($log->getData()) {
                // Action Create o Update
                $txtnulo = ($log->getAction() == 'create') ? '--' : 'Dato eliminado';
                // Datos simples
                $nombre = (array_key_exists('nombre', $log->getData())) ? (( $log->getData()['nombre'] ) ? $log->getData()['nombre'] : $txtnulo ) : NULL;
                $codigo = (array_key_exists('codigo', $log->getData())) ? (( $log->getData()['codigo'] ) ? $log->getData()['codigo'] : $txtnulo ) : NULL;
                $barcode = (array_key_exists('barcode', $log->getData())) ? (( $log->getData()['barcode'] ) ? $log->getData()['barcode'] : $txtnulo ) : NULL;
                $stockMinimo = (array_key_exists('stockMinimo', $log->getData())) ? (( $log->getData()['stockMinimo'] ) ? $log->getData()['stockMinimo'] : $txtnulo ) : NULL;
                // -- Tipo
                if (array_key_exists('tipo', $log->getData())) {
                    $tipo = $log->getData()['tipo'];
                    if ($tipo) {
                        $tipoObj = $em->getRepository('ConfigBundle:Tipo')->find($tipo['id']);
                        $tipoNombre = $tipoObj->getNombre();
                    }
                    else {
                        $tipoNombre = $txtnulo;
                    }
                }
                else {
                    $tipoNombre = NULL;
                }
                // -- Marca
                if (array_key_exists('marca', $log->getData())) {
                    $marca = $log->getData()['marca'];
                    if ($marca) {
                        $marcaObj = $em->getRepository('ConfigBundle:Marca')->find($marca['id']);
                        $marcaNombre = $marcaObj->getNombre();
                    }
                    else {
                        $marcaNombre = $txtnulo;
                    }
                }
                else {
                    $marcaNombre = NULL;
                }
                // -- Modelo
                if (array_key_exists('modelo', $log->getData())) {
                    $modelo = $log->getData()['modelo'];
                    if ($modelo) {
                        $modeloObj = $em->getRepository('ConfigBundle:Modelo')->find($modelo['id']);
                        $modeloNombre = $modeloObj->getNombre();
                    }
                    else {
                        $modeloNombre = $txtnulo;
                    }
                }
                else {
                    $modeloNombre = NULL;
                }
                $data = array('nombre' => $nombre, 'codigo' => $codigo, 'barcode' => $barcode, 'stockMinimo' => $stockMinimo,
                    'tipo' => $tipoNombre, 'marca' => $marcaNombre, 'modelo' => $modeloNombre);
            }
            else {
                // Action Remove
                $data = NULL;
            }
            $action = ($log->getAction() == 'create') ? 'Creación' : (($log->getAction() == 'update') ? 'Actualización' : 'Borrado');
            $item = array('id' => $log->getObjectId(), 'action' => $action, 'loggedAt' => $log->getLoggedAt(),
                'username' => $log->getUsername(), 'data' => $data);
            array_push($items, $item);
        }
        $html = $this->renderView('AppBundle:Insumo:partial-logs.html.twig', array('logs' => $items));
        return new Response($html);
    }

    /*
     * BACKUP PROCESO APROBAR INSUMOS ANTES DE MODIFICAR
     */

    /**
     * @Route("/XupdateSolicitud", name="Xupdate_solicitud")
     * @Method("POST")
     */
    /* public function XupdateSolicitudAction(Request $request) {
      die;
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $tipo = $request->get('tipo');
      var_dump($request->get('cantidad'));
      $cant = number_format($request->get('cantidad'), 2, '.', '');
      var_dump($cant);
      die;
      $insumoId = $request->get('insumo');
      $depositoId = $request->get('dep');
      $entity = $em->getRepository('AppBundle:InsumoxTarea')->find($id);
      $entity->setFechaAutorizado(new \DateTime());
      $entity->setAutorizante($this->getUser());
      if ($tipo) {
      $entity->setCantidadAprobada(($tipo == 'APROBAR') ? $entity->getCantidad() : 0 );
      $insumo = ($entity->getInsumo()) ? $entity->getInsumo() : null;
      }
      else {
      $insumo = $em->getRepository('AppBundle:Insumo')->find($insumoId);
      $entity->setInsumo($insumo);
      $entity->setCantidadAprobada($cant);
      }
      try {
      $em->getConnection()->beginTransaction();
      $em->persist($entity);
      $em->flush();
      if ($entity->getCantidadAprobada() > 0) {
      // actualizar stock
      $deposito = $em->getRepository('ConfigBundle:Departamento')->find($depositoId);
      $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($entity->getInsumo()->getId(), $deposito->getId());
      if ($stock) {
      $saldo = $stock->getCantidad() - $entity->getCantidadAprobada();
      // controlar que no quede en negativo el stock
      if ($saldo < 0) {
      // no hay stock.
      $em->getConnection()->rollback();
      return new Response(json_encode('SINSTOCK'));
      }
      else {
      $stock->setCantidad($stock->getCantidad() - $entity->getCantidadAprobada());
      $em->persist($stock);
      // Cargar movimiento
      $movim = new StockHistorico();
      $movim->setFecha(new \DateTime());
      $movim->setTipo('SOPORTE');
      $movim->setSigno('-');
      $movim->setMovimiento($entity->getId());
      $movim->setInsumo($insumo);
      $movim->setStock($insumo->getStockTotal());
      $movim->setCantidad($entity->getCantidadAprobada());
      $movim->setDeposito($deposito);
      $em->persist($movim);
      $em->flush();
      }
      }

      }
      // generar mensajería
      $textoMensaje = 'Solicitud del ' . $entity->getCreated()->format('d/m/Y') .
      ' por "' . $entity->getCantidad() . '" de "';
      if ($insumo)
      $insumoTxt = $insumo->getTexto();
      else
      $insumoTxt = $entity->getDescripcion();
      $textoMensaje = $textoMensaje . $insumoTxt . '"';
      $asunto = 'Insumo Aprobado';
      switch ($entity->getEstado()) {
      case 'R':
      $span = '<span style="font-weight:normal;" class="badge bg-red">Rechazado</span>';
      $respuesta = ' se ha rechazado.';
      $asunto = 'Insumo Rechazado';
      break;
      case 'AP':
      $span = '<span style="font-weight:normal;" class="badge bg-orange">Aprobación Parcial</span>';
      $respuesta = ' se ha aprobado parcialmente.';
      break;
      case 'AT':
      $span = '<span style="font-weight:normal;" class="badge bg-green">Aprobado</span>';
      $respuesta = ' se ha aprobado en su totalidad.';
      break;
      }
      $textoMensaje = $textoMensaje . $respuesta;
      $mensaje = new Mensajeria();
      $mensaje->setDestinatario($entity->getCreatedBy());
      $mensaje->setAsunto($asunto);
      $mensaje->setMensaje($textoMensaje);
      $em->persist($mensaje);
      $em->flush();

      $data = array(
      'span' => $span,
      'nombre' => ($insumo) ? $insumo->getTexto() : null,
      'stock' => ($insumo) ? $insumo->getStockByDeposito($deposito->getId()) : null);
      $em->getConnection()->commit();
      return new Response(json_encode($data));
      }
      catch (\Exception $ex) {
      $em->getConnection()->rollback();
      //var_dump($ex->getMessage());
      return new Response(json_encode('ERROR'));
      }
      } */

    /**
     * @Route("/getInsumosxDeposito", name="get_insumos_x_deposito")
     * @Method("POST")
     */
    public function getInsumosxDeposito(Request $request) {
        $id = $request->get('deposito_id');
        $em = $this->getDoctrine()->getManager();
        $insumos = $em->getRepository('AppBundle:Insumo')->findByDeposito($id);
        return new JsonResponse($insumos);
    }

    /**
     * @Route("/findBarcodeById", name="find_barcode_by_id")
     * @Method("GET")
     */
    public function findBarcodeById(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $insumo = $em->getRepository('AppBundle:Insumo')->find($id);
        return new Response(($insumo) ? $insumo->getBarcode() : null);
    }

    /**
     * @Route("/findInsumoIdByBarcode", name="find_insumo_id_by_barcode")
     * @Method("GET")
     */
    public function findInsumoIdByBarcode(Request $request) {
        $barcode = $request->get('bc');
        $em = $this->getDoctrine()->getManager();
        $insumo = $em->getRepository('AppBundle:Insumo')->findOneByBarcode($barcode);
        return new Response(($insumo) ? $insumo->getId() : null);
    }

    /**
     * @Route("/getStockByDeposito", name="get_stock_by_deposito")
     * @Method("GET")
     */
    public function getStockByDeposito(Request $request) {
        $id = $request->get('id');
        $deposito = $request->get('deposito');
        $em = $this->getDoctrine()->getManager();
        $insumo = $em->getRepository('AppBundle:Insumo')->find($id);
        return new Response(($insumo) ? $insumo->getStockByDeposito($deposito) : '');
    }

}