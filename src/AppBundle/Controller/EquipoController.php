<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\Equipo;
use AppBundle\Form\EquipoType;
use AppBundle\Entity\EquipoUbicacion;

/**
 * @Route("/equipo")
 */
class EquipoController extends Controller {

    /**
     * @Route("/busquedarapida", name="busqueda_rapida")
     * @Method("GET")
     */
    public function busquedaRapidaAction(Request $request) {
        $list = $request->get('list');
        return false;
    }

    /**
     * @Route("/renderSearchEquipo", name="render_search_equipo")
     * @Method("GET")
     */
    public function renderSearchEquipoAction() {
        $partial = $this->renderView('AppBundle:Equipo:partial-search-equipo.html.twig');
        return new Response($partial);
    }

    /**
     * @Route("/equipoSearchDatatables", name="equipo_search_datatables")
     * @Method("POST")
     * @Template()
     */
    public function equipoSearchDatatablesAction(Request $request) {
        // Set up required variables
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->repository = $this->entityManager->getRepository('AppBundle:Equipo');
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

        // Process Parameters
        // Orders
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = "array or whatever is needed";

        // usuario para restringir edificios
        $userId = $this->getUser()->getId();
        // Get results from the Repository
        $results = $this->repository->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions = null, $userId);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $this->repository->count($this->getUser()->getId());

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

        foreach ($objects as $key => $equipo) {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column) {
                // In all cases where something does not exist or went wrong, return -
                $responseTemp = "-";

                switch ($column['name']) {
                    case 'tipo': {
                            $tipo = $equipo->getTipo();
                            // This cannot happen if inner join is used
                            // However it can happen if left or right joins are used
                            if ($tipo !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $tipo->getNombre()));
                            }
                            break;
                        }
                    case 'nroSerie': {
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $equipo->getNroSerie()));
                            break;
                        }
                    case 'nombre': {
                            $name = $equipo->getNombre();

                            // Do this kind of treatments if you suspect that the string is not JS compatible
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $name));

                            // View permission ?
                            /* if ($this->get('security.authorization_checker')->isGranted('view_town', $town))
                              {
                              // Get the ID
                              $id = $town->getId();
                              // Construct the route
                              $url = '';
                              //$this->generateUrl('playground_town_view', array('id' => $id));
                              // Construct the html code to send back to datatables
                              $responseTemp = "<a href='".$url."' target='_self'>".$ref."</a>";
                              }
                              else
                              {
                              $responseTemp = $name;
                              } */
                            break;
                        }
                    case 'marca': {
                            $marca = $equipo->getMarca();
                            if ($marca !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $marca->getNombre()));
                            }
                            break;
                        }
                    case 'modelo': {
                            $modelo = $equipo->getModelo();
                            if ($modelo !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r"), ' ', $modelo->getNombre()));
                            }
                            break;
                        }
                    case 'estado': {
                            $estado = $equipo->getEstado();
                            if ($estado !== null) {
                                $responseTemp = $estado->getNombre();
                            }
                            break;
                        }
                    case 'ubicacion': {
                            if ($equipo->getUbicacionActual() !== null) {
                                $responseTemp = $equipo->getUbicacionActual()->getTexto();
                            }
                            else {
                                $responseTemp = 'Sin ubicación';
                            }
                            break;
                        }
                    case 'checks': {
                            $responseTemp = "<input class='ckItem' type='checkbox' data-id='" . $equipo->getId() . "' data-barcode='" . $equipo->getBarcode() . "' value='" . $equipo->getId() . "' />";
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
     * @Route("/", name="equipo")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'equipo');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_equipo');
        $estadoDefault = $em->getRepository('ConfigBundle:Estado')->findOneByNombre('Operativo')->getId();

        switch ($request->get('_opFiltro')) {
            case 'limpiar':
                $filtro = array('selTipos' => NULL, 'idMarca' => 0, 'idModelo' => 0, 'idEstado' => $estadoDefault,
                    'idUbicacion' => 0, 'idEdificio' => 0, 'idDepartamento' => 0, 'idPiso' => 0, 'verificado' => 'T',
                    'opAdicional' => 0, 'txtAdicional' => '', 'fechaDesde' => '', 'fechaHasta' => '');
                break;
            case 'buscar':
                $filtro = array(
                    'selTipos' => $request->get('selTipos'),
                    //'idTipo'  =>$request->get('idTipo'),
                    'idMarca' => $request->get('idMarca'),
                    'idModelo' => $request->get('idModelo'),
                    'idEstado' => ($request->get('idEstado') ? $request->get('idEstado') : $estadoDefault ),
                    'idUbicacion' => $request->get('idUbicacion'),
                    'idEdificio' => $request->get('idEdificio'),
                    'idDepartamento' => $request->get('idDepartamento'),
                    'idPiso' => $request->get('idPiso'),
                    'verificado' => (is_null($request->get('verificado')) ? 'T' : $request->get('verificado') ),
                    'opAdicional' => $request->get('opAdicional'),
                    'txtAdicional' => $request->get('txtAdicional'),
                    'fechaDesde' => $request->get('fechaDesde'),
                    'fechaHasta' => $request->get('fechaHasta')
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $filtro = array(
                        'selTipos' => $sessionFiltro['selTipos'],
                        //'idTipo'  =>$sessionFiltro['idTipo'],
                        'idMarca' => $sessionFiltro['idMarca'],
                        'idModelo' => $sessionFiltro['idModelo'],
                        'idEstado' => ($sessionFiltro['idEstado']) ? $sessionFiltro['idEstado'] : $estadoDefault,
                        'idUbicacion' => $sessionFiltro['idUbicacion'],
                        'idEdificio' => $sessionFiltro['idEdificio'],
                        'idDepartamento' => $sessionFiltro['idDepartamento'],
                        'idPiso' => $sessionFiltro['idPiso'],
                        'verificado' => $sessionFiltro['verificado'],
                        'opAdicional' => $sessionFiltro['opAdicional'],
                        'txtAdicional' => $sessionFiltro['txtAdicional'],
                        'fechaDesde' => $sessionFiltro['fechaDesde'],
                        'fechaHasta' => $sessionFiltro['fechaHasta']
                    );
                }
                else {
                    //,'idTipo'=>0
                    $filtro = array('selTipos' => NULL, 'idMarca' => 0, 'idModelo' => 0, 'idEstado' => $estadoDefault,
                        'idUbicacion' => 0, 'idEdificio' => 0, 'idDepartamento' => 0, 'idPiso' => 0, 'verificado' => 'T',
                        'opAdicional' => 0, 'txtAdicional' => '', 'fechaDesde' => '', 'fechaHasta' => '');
                }
                break;
        }

        $session->set('filtro_equipo', $filtro);
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $userId = $this->getUser()->getId();
        $adicionales = array('CÓDIGO DE BARRA', 'PROVEEDOR', 'FECHA DE COMPRA', 'N° ORDEN COMPRA', 'N° FACTURA', 'N° REMITO');
        $entities = $em->getRepository('AppBundle:Equipo')->findByCriteria($filtro);
        $summary = $em->getRepository('AppBundle:Equipo')->findSummaryByCriteria($filtro, $userId);
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }

        $tipos = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT t.id,t.nombre', 't.nombre', 'tipo', $userId);
        //$tipos = $em->getRepository('AppBundle:Equipo')->findComboTipo($filtro);
        //$marcas = $em->getRepository('AppBundle:Equipo')->findComboMarca($filtro);
        $marcas = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT ma.id,ma.nombre', 'ma.nombre', 'marca', $userId);
        if ($filtro['idMarca']) {
            $modelos = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT mo.id,mo.nombre', 'mo.nombre', 'modelo', $userId);
            //$modelos = $em->getRepository('AppBundle:Equipo')->findComboModelo($filtro);
        }
        else {
            $modelos = NULL;
        }
        $estados = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT es.id,es.nombre', 'es.nombre', 'estado', $userId);
        //$estados = $em->getRepository('AppBundle:Equipo')->findComboEstado($filtro);
        $ubicaciones = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT u.id,u.abreviatura', 'u.abreviatura', 'ubicacion', $userId);
        $edificios = $departamentos = $pisos = NULL;
        if ($filtro['idUbicacion']) {
            $edificios = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT ed.id,ed.nombre', 'ed.nombre', 'edificio', $userId);
            if ($filtro['idEdificio']) {
                $departamentos = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT d.id,d.nombre', 'd.nombre', 'departamento', $userId);
                $pisos = $em->getRepository('AppBundle:Equipo')->findCombosByCriteria($filtro, 'DISTINCT p.id,p.nombre', 'p.nombre', 'piso', $userId);
            }
        }

        return array(
            'entities' => $entities,
            'tipos' => $tipos,
            'marcas' => $marcas,
            'modelos' => $modelos,
            'estados' => $estados,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'pisos' => $pisos,
            'adicionales' => $adicionales,
            'deleteForms' => $deleteForms,
            'summary' => $summary
        );
    }

    /**
     * @Route("/", name="equipo_create")
     * @Method("PUT")
     * @Template("AppBundle:Equipo:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'equipo_new');
        $data = $request->get('appbundle_equipo');
        $new = $data['savenew'];
        $entity = new Equipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($entity);
                $em->flush();
                $entity->setBarcode(str_pad($entity->getTipo()->getId(), 3, '0', STR_PAD_LEFT) .
                        str_pad($entity->getMarca()->getId(), 3, '0', STR_PAD_LEFT) . str_pad($entity->getModelo()->getId(), 3, '0', STR_PAD_LEFT) .
                        str_pad($entity->getId(), 5, '0', STR_PAD_LEFT));
                $em->persist($entity);
                $em->flush();
                if ($new == 'S') {
                    $this->addFlash('success', 'El equipo fue creado. Puede crear el siguiente!');
                    return $this->redirectToRoute('equipo_new');
                }
                else {
                    $this->addFlash('success', 'El equipo fue creado!');
                    return $this->redirectToRoute('equipo');
                }
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return array(
            'entity' => $entity,
            'reubicacion' => false,
            'form' => $form->createView(),
        );
    }

    /**
     * @param Equipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Equipo $entity) {
        $form = $this->createForm(new EquipoType(), $entity, array(
            'action' => $this->generateUrl('equipo_create'),
            'method' => 'PUT'
        ));
        return $form;
    }

    /**
     * @Route("/new", name="equipo_new")
     * @Method("GET")
     * @Template("AppBundle:Equipo:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), 'equipo_new');
        $entity = new Equipo();
        $ubicacion = new EquipoUbicacion();
        $em = $this->getDoctrine()->getManager();
        $estado = $em->getRepository('ConfigBundle:Estado')->findOneByInicial(1);
        $entity->setEstado($estado);
        $ubicacion->setActual(TRUE);
        $ubicacion->setFechaEntrega(new \DateTime());
        $conceptoEntrega = $em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByInicial(1);
        $ubicacion->setConceptoEntrega($conceptoEntrega);
        $entity->addUbicacion($ubicacion);
        $form = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'reubicacion' => false,
                //'lista_ubicaciones' => json_encode($this->getUser()->getUbicacionesPermitidas()),
        );
    }

    /**
     * @Route("/{id}/edit", name="equipo_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'equipo_edit');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Equipo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el equipo.');
        }
        // Si no tiene ubicaciÃ³n actual
        $reubicacion = ( count($entity->getUbicaciones()) > 0 ) ? TRUE : FALSE;

        if (count($entity->getUbicaciones()) == 0) {
            $ubicacion = new EquipoUbicacion();
            /* $ubic = $em->getRepository('ConfigBundle:Ubicacion')->find(1);
              $ubicacion->setUbicacion($ubic);
              $ubicacion->setFechaEntrega(new \DateTime());
             */
            $ubicacion->setActual(TRUE);
            $entity->addUbicacion($ubicacion);
        }
        else {
            /* foreach ($entity->getUbicaciones() as $ubicacion) {
              $dpto = $ubicacion->getDepartamento();
              if ($dpto) {
              if ($ubicacion->getEdificio()->getId() != $dpto->getEdificio()->getId()) {
              $ubicacion->setEdificio($dpto->getEdificio());
              $ubicacion->setUbicacion($dpto->getEdificio()->getUbicacion());
              $em->persist($entity);
              $em->flush();
              }
              }
              } */
        }

        /* $entity->setBarcode(  str_pad($entity->getTipo()->getId(),3,'0',STR_PAD_LEFT) .
          str_pad($entity->getMarca()->getId(),3,'0',STR_PAD_LEFT) .str_pad($entity->getModelo()->getId(),3,'0',STR_PAD_LEFT).
          str_pad($entity->getId(),5,'0',STR_PAD_LEFT) ); */
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        //$repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
        //$item = $em->find('AppBundle\Entity\Insumo', $id /*article id*/);
        //$logs = $repo->getLogEntries($item);
        $fechaInstalacion = $em->getRepository('AppBundle:Equipo')->getFechaInstalacion($id);

        return array(
            'fechaInstalacion' => $fechaInstalacion['fecha'],
            'entity' => $entity,
            'form' => $editForm->createView(),
            'reubicacion' => $reubicacion,
            'delete_form' => $deleteForm->createView(),
                //'lista_ubicaciones' => json_encode($this->getUser()->getUbicacionesPermitidas()),
        );
    }

    /**
     * @param Equipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Equipo $entity) {
        $form = $this->createForm(new EquipoType(), $entity, array(
            'action' => $this->generateUrl('equipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="equipo_update")
     * @Method("PUT")
     * @Template("AppBundle:Equipo:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'equipo_edit');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Equipo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el equipo.');
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            try {
                // dejar solo el último como actual para los casos en que guarda mal el actual.
                $actual = $entity->getUbicacionActual();
                foreach ($entity->getUbicaciones() as $ubic) {
                    if ($ubic != $actual) {
                        $ubic->setActual(0);
                    }
                }
                $em->flush();
                // $this->addFlash('success','El equipo se modificó con éxito!');
                return $this->redirectToRoute('equipo');
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        $deleteForm = $this->createDeleteForm($id);
        $reubicacion = ( count($entity->getUbicaciones()) > 0 ) ? TRUE : FALSE;
        return array(
            'entity' => $entity,
            'reubicacion' => $reubicacion,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
                //'lista_ubicaciones' => json_encode($this->getUser()->getUbicacionesPermitidas()),
        );
    }

    /**
     * @Route("/delete/{id}", name="equipo_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'equipo_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity = $em->getRepository('AppBundle:Equipo')->find($id);
                if (!$entity) {
                    throw $this->createNotFoundException('No existe el equipo.');
                }
                if (is_null($entity->getDeletedAt())) {
                    //forzar el guardado de ultima fecha de modificaciÃ³n antes de softdelete
                    $em->getFilters()->enable('softdeleteable');
                    $entity->setUpdated(new \DateTime());
                    $em->persist($entity);
                    $em->flush();
                }
                $em->remove($entity);
                $em->flush();
                $em->getConnection()->commit();
                $this->addFlash('success', 'El equipo fue eliminado!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }

        return $this->redirectToRoute('equipo');
    }

    /**
     * @Route("/deleteAjax/{id}", name="equipo_delete_ajax")
     * @Method("GET")
     */
    public function deleteAjaxAction($id) {
        UtilsController::haveAccess($this->getUser(), 'equipo_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        try {
            $em->getConnection()->beginTransaction();
            $entity = $em->getRepository('AppBundle:Equipo')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('No existe el equipo.');
            }
            if (is_null($entity->getDeletedAt())) {
                //forzar el guardado de ultima fecha de modificaciÃ³n antes de softdelete
                $em->getFilters()->enable('softdeleteable');
                $entity->setUpdated(new \DateTime());
                $em->persist($entity);
                $em->flush();
            }
            $em->remove($entity);
            $em->flush();
            $em->getConnection()->commit();
            $this->addFlash('success', 'El equipo fue eliminado!');
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
        }
        return $this->redirectToRoute('equipo');
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('equipo_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * @Route("/getDataEquipo", name="datos_equipo")
     * @Method("POST")
     */
    public function getDataEquipoAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $equipo = $em->getRepository('AppBundle:Equipo')->find($id);
        $html = $this->renderView('AppBundle:Equipo:partial-datos-equipo.html.twig',
                array('datos' => $equipo)
        );
        return new Response($html);
    }

    /**
     * @Route("/getEquiposxDepartamento", name="get_equipos_x_departamento")
     * @Method("POST")
     */
    public function getEquiposxDepartamento(Request $request) {
        $id = $request->get('depto_id');
        $em = $this->getDoctrine()->getManager();
        $equipos = $em->getRepository('AppBundle:Equipo')->findByDepartamento($id);
        return new JsonResponse($equipos);
    }

    /**
     * @Route("/getEquiposStockTecnico", name="get_equipos_stocktecnico")
     * @Method("POST")
     */
    public function getEquiposStockTecnico() {
        $em = $this->getDoctrine()->getManager();
        $equipos = $em->getRepository('AppBundle:Equipo')->findByStockTecnico();
        return new JsonResponse($equipos);
    }

    /**
     * @Route("/findEquipoIdByBarcode", name="find_equipo_id_by_barcode")
     * @Method("GET")
     */
    public function findEquipoIdByBarcode(Request $request) {
        $barcode = $request->get('bc');
        $em = $this->getDoctrine()->getManager();
        $equipo = $em->getRepository('AppBundle:Equipo')->findOneByBarcode($barcode);
        return new Response(($equipo) ? $equipo->getId() : null);
    }

    /**
     * @Route("/findEquipoByBarcode", name="find_equipo_by_barcode")
     * @Method("GET")
     */
    public function findEquipoByBarcode(Request $request) {
        $barcode = $request->get('bc');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $equipo = $em->getRepository('AppBundle:Equipo')->findOneByBarcode($barcode);
        if ($equipo) {
            $enOrdenAbierta = $em->getRepository('AppBundle:Equipo')->checkEnOrdenAbierta($equipo->getId(), $id);
            if ($enOrdenAbierta) {
                return new Response(json_encode(null));
            }
            else {
                return new Response(json_encode(array('id' => $equipo->getId(), 'nombre' => $equipo->getTextoOT())));
            }
        }
        else {
            return new Response(json_encode(null));
        }
    }

    /**
     * @Route("/findBarcodeById", name="find_barcode_by_id")
     * @Method("GET")
     */
    public function findBarcodeById(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $equipo = $em->getRepository('AppBundle:Equipo')->find($id);
        return new Response(($equipo) ? $equipo->getBarcode() : null);
    }

    /**
     * @Route("/findEquipoDataById", name="find_equipo_data_by_id")
     * @Method("GET")
     */
    public function findEquipoDataById(Request $request) {
        // Busca equipo para asociar a requerimiento
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $equipo = $em->getRepository('AppBundle:Equipo')->find($id);
        $enOrdenAbierta = $em->getRepository('AppBundle:Equipo')->checkEnOrdenAbierta($id);
        if ($equipo->getEnRequerimientoAbierto() || $enOrdenAbierta) {
            $data = json_encode(array('msj' => 'El equipo ya se encuentra asignado'));
        }
        else {
            $data = json_encode(array('msj' => 'OK', 'id' => $equipo->getId(), 'nombre' => $equipo->getTextoOT(), 'barcode' => $equipo->getBarcode(), 'descripcion' => $equipo->getNombre()));
        }
        return new Response(($equipo) ? $data : null);
    }

    /**
     * @Route("{id}/pdfFichaEquipo.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ficha_equipo")
     * @Method("GET")
     */
    public function pdfFichaEquipoAction($id) {
        $em = $this->getDoctrine()->getManager();
        $eq = $em->getRepository('AppBundle:Equipo')->find($id);
        $requerimientos = $em->getRepository('AppBundle:Requerimiento')->getReqyOts($id);
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $em->getFilters()->disable('softdeleteable');
        if ($eq->getModelo()->getAbsolutePath()) {
            $foto = $eq->getModelo()->getAbsolutePath();
        }
        else {
            $foto = __DIR__ . '/../../../web/uploads/empty.jpg';
        }

        $this->render('AppBundle:Equipo:ficha.pdf.twig',
                array('eq' => $eq, 'requerimientos' => $requerimientos,
                    'logo' => $logo1, 'foto' => $foto), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=ficha_equipo_' . trim($eq->getNombre()) . '.pdf'));
    }

    /**
     * @Route("/getEquipoLogs", name="get_equipo_logs")
     * @Method("GET")
     * @Template()
     */
    public function getEquipoLogs(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'view_logs');
        $id = $request->get('id');
        $entity = "AppBundle\\Entity\\Equipo";
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
                $nroSerie = (array_key_exists('nroserie', $log->getData())) ? (( $log->getData()['nroserie'] ) ? $log->getData()['nroserie'] : $txtnulo ) : NULL;
                $fechaCompra = (array_key_exists('fechaCompra', $log->getData())) ? (( $log->getData()['fechaCompra'] ) ? $log->getData()['fechaCompra'] : $txtnulo ) : NULL;
                $nroFactura = (array_key_exists('nroFactura', $log->getData())) ? (( $log->getData()['nroFactura'] ) ? $log->getData()['nroFactura'] : $txtnulo ) : NULL;
                $nroOrdenCompra = (array_key_exists('nroOrdenCompra', $log->getData())) ? (( $log->getData()['nroOrdenCompra'] ) ? $log->getData()['nroOrdenCompra'] : $txtnulo ) : NULL;
                $nroRemito = (array_key_exists('nroRemito', $log->getData())) ? (( $log->getData()['nroRemito'] ) ? $log->getData()['nroRemito'] : $txtnulo ) : NULL;
                $verificado = (array_key_exists('verificado', $log->getData())) ? (( $log->getData()['verificado'] ) ? $log->getData()['verificado'] : $txtnulo ) : NULL;
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
                // -- Estado
                if (array_key_exists('estado', $log->getData())) {
                    $estado = $log->getData()['estado'];
                    if ($estado) {
                        $estadoObj = $em->getRepository('ConfigBundle:Estado')->find($estado['id']);
                        $estadoNombre = $estadoObj->getNombre();
                    }
                    else {
                        $estadoNombre = $txtnulo;
                    }
                }
                else {
                    $estadoNombre = NULL;
                }
                // -- Proveedor
                if (array_key_exists('proveedor', $log->getData())) {
                    $proveedor = $log->getData()['proveedor'];
                    if ($proveedor) {
                        $proveedorObj = $em->getRepository('AppBundle:Proveedor')->find($proveedor['id']);
                        $proveedorNombre = $proveedorObj->getNombre();
                    }
                    else {
                        $proveedorNombre = $txtnulo;
                    }
                }
                else {
                    $proveedorNombre = NULL;
                }
                $data = array('nombre' => $nombre, 'codigo' => $codigo, 'barcode' => $barcode, 'nroserie' => $nroSerie,
                    'tipo' => $tipoNombre, 'marca' => $marcaNombre, 'modelo' => $modeloNombre, 'estado' => $estadoNombre,
                    'fechacompra' => $fechaCompra, 'proveedor' => $proveedorNombre, 'nroremito' => $nroRemito, 'nrofactura' => $nroFactura,
                    'nrocompra' => $nroOrdenCompra, 'verificado' => $verificado);
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
        $html = $this->renderView('AppBundle:Equipo:partial-logs.html.twig', array('logs' => $items));
        return new Response($html);
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printListadoEquipos", name="print_listado_equipos")
     * @Method("POST")
     * @Template()
     */
    public function printListadoEquiposAction(Request $request) {

        $op = $request->get('option');
        $em = $this->getDoctrine()->getManager();
        //$items = $request->get('datalist');
        $session = $this->get('session');
        $filtro = $session->get('filtro_equipo');
        $searchTerm = $request->get('searchterm');

        $tipos = $this->getTextoFiltro($em, $filtro['selTipos']);
        $marca = $em->getRepository('ConfigBundle:Marca')->find($filtro['idMarca']);
        $modelo = $em->getRepository('ConfigBundle:Modelo')->find($filtro['idModelo']);
        $estado = $em->getRepository('ConfigBundle:Estado')->find($filtro['idEstado']);
        $ubicacion = $em->getRepository('ConfigBundle:Ubicacion')->find($filtro['idUbicacion']);
        $edificio = $em->getRepository('ConfigBundle:Edificio')->find($filtro['idEdificio']);
        $departamento = $em->getRepository('ConfigBundle:Departamento')->find($filtro['idDepartamento']);
        $verificado = ($filtro['verificado'] == 'T') ? 'Todos' : ($filtro['verificado'] == '1') ? 'Si' : 'No';
        $adicionales = array('CÓDIGO DE BARRA', 'PROVEEDOR', 'FECHA DE COMPRA', 'N° ORDEN COMPRA', 'N° FACTURA', 'N° REMITO');
        $opAdicional = $adicionales[$filtro['opAdicional']];

        $arrayFiltro = array($tipos ? $tipos : 'Todos', $marca ? $marca->getNombre() : 'Todas',
            $modelo ? $modelo->getNombre() : 'Todos', $estado ? $estado->getNombre() : 'Todos',
            $ubicacion ? $ubicacion->getAbreviatura() : 'Todas', $edificio ? $edificio->getNombre() : 'Todos',
            $departamento ? $departamento->getNombre() : 'Todos', $verificado,
            $opAdicional, $filtro['txtAdicional'], $filtro['fechaDesde'], $filtro['fechaHasta']);

        $hoy = new \DateTime();
        $userId = $this->getUser()->getId();
        switch ($op) {
            case 'pdf':
                $entities = $em->getRepository('AppBundle:Equipo')->findSqlByCriteria($filtro, 'RES', $searchTerm, $userId);
                $logo1 = __DIR__ . '/../../../web/bundles/app/img/pdf_logo.png';
                $facade = $this->get('ps_pdf.facade');
                $response = new Response();
                $this->render('AppBundle:Equipo:listado.pdf.twig', array('items' => $entities, 'filtro' => $arrayFiltro, 'logo' => $logo1,
                    'search' => $request->get('searchterm')), $response);

                $xml = $response->getContent();
                $content = $facade->render($xml);

                return new Response($content, 200, array('content-type' => 'application/pdf',
                    'Content-Disposition' => 'filename=listado_equipos_' . $hoy->format('dmY_Hi') . '.pdf'));

            case 'xls':
                $entities = $em->getRepository('AppBundle:Equipo')->findSqlByCriteria($filtro, 'RES', $searchTerm, $userId);
                $partial = $this->renderView('AppBundle:Equipo:listado-xls.html.twig',
                        array('items' => $entities, 'filtro' => $arrayFiltro, 'search' => $searchTerm));

                $fileName = 'Listado_Equipos_' . $hoy->format('dmY_Hi');
                $response = new Response();
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
                $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
                $response->setContent($partial);
                return $response;
            case 'csv':
                $sql = $em->getRepository('AppBundle:Equipo')->findSqlByCriteria($filtro, 'SQL', $searchTerm, $userId);
                $conn = $this->get('database_connection');
                // Query data from database
                $results = $conn->query($sql);
                $strResponse = new StreamedResponse(function() use($results) {
                    $handle = fopen('php://output', 'w+');
                    // Add the header
                    fputcsv($handle, ['codigobarra', 'tipo', 'nroSerie', 'descripcion', 'marca', 'modelo', 'estado', 'ubicacion',
                        'edificio', 'departamento', 'piso', 'verificado'], ';');
                    while ($row = $results->fetch()) {
                        fputcsv($handle, array($row['barcode0'], $row['nombre1'], $row['nro_serie2'], $row['nombre3'], $row['nombre4'],
                            $row['nombre5'], $row['nombre6'], $row['abreviatura7'], $row['nombre8'], $row['nombre9'],
                            $row['nombre10'], $row['verificado11']), ';');
                    }
                    fclose($handle);
                });

                $strResponse->setStatusCode(200);
                $strResponse->headers->set('Content-Type', 'text/csv; charset=utf-8');
                $fileName = 'Listado_Equipos_' . $hoy->format('dmY_Hi');
                $strResponse->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '.csv"');
                return $strResponse->send();
        }
    }

    /**
     * @Route("/importarDatos", name="import_data")
     * @Method("GET")
     * @Template()
     */
    public function importarDatos() {
        return false;
        $em = $this->getDoctrine()->getManager();
        $importados = $em->getRepository('ConfigBundle:Importados')->findAll();
        echo count($importados);
        echo '<br>';
        foreach ($importados as $imp) {
            try {
                $em->getConnection()->beginTransaction();
                echo $imp->getId() . ' * ' . $imp->getDescripcion() . ' * ' . $imp->getNroserie();
                // tipo de equipo
                $tipoTxt = trim($imp->getTipo());
                $tipo = $em->getRepository('ConfigBundle:Tipo')->findOneByNombre($tipoTxt);
                if (!$tipo) {
                    //insertar
                    $tipo = new Tipo();
                    $tipo->setClase('E');
                    $tipo->setNombre($tipoTxt);
                    $em->persist($tipo);
                    $em->flush();
                }
                //$idTipo = $tipo->getId();
                // marca
                $marcaTxt = trim($imp->getMarca());
                $marca = $em->getRepository('ConfigBundle:Marca')->findOneByNombre($marcaTxt);
                if (!$marca) {
                    //insertar
                    $marca = new Marca();
                    $marca->setNombre($marcaTxt);
                    $em->persist($marca);
                    $em->flush();
                }
                //$idMarca = $marca->getId();
                // modelo
                $modeloTxt = trim($imp->getModelo());
                $modelo = $em->getRepository('ConfigBundle:Modelo')->findOneByNombre($modeloTxt);
                if (!$modelo) {
                    //insertar
                    $modelo = new Modelo();
                    $modelo->setNombre($modeloTxt);
                    $modelo->setMarca($marca);
                    $em->persist($modelo);
                    $em->flush();
                }
                //$idModelo = $modelo->getId();
                // estado
                $estado = $em->getRepository('ConfigBundle:Estado')->find($imp->getEstadoId());

                // cargar equipo
                $equipo = new Equipo();
                $equipo->setNombre(trim($imp->getDescripcion()));
                $equipo->setTipo($tipo);
                $equipo->setMarca($marca);
                $equipo->setModelo($modelo);
                $equipo->setEstado($estado);
                $equipo->setNroSerie(trim($imp->getNroserie()));
                $equipo->setObservaciones(trim($imp->getObservaciones()));
                $equipo->setImportado($imp);
                $em->persist($equipo);
                $em->flush();
                echo ' - Creado equipo ';

                // crear ubicacion
                $ubicacion = new EquipoUbicacion;
                $ubicacion->setActual(true);
                // concepto entrega
                $concepto = $em->getRepository('ConfigBundle:ConceptoEntrega')->findOneByInicial(1);
                $ubicacion->setConceptoEntrega($concepto);
                // ubicacion
                $ubic = $em->getRepository('ConfigBundle:Ubicacion')->find($imp->getUbicacionId());
                $ubicacion->setUbicacion($ubic);
                // edificio
                $edif = $em->getRepository('ConfigBundle:Edificio')->find($imp->getEdificioId());
                $ubicacion->setEdificio($edif);
                // departamento
                $deptoTxt = trim($imp->getDepartamento());
                $depto = $em->getRepository('ConfigBundle:Departamento')->findOneByNombre($deptoTxt);
                if (!$depto) {
                    //insertar
                    $depto = new Departamento();
                    $depto->setNombre($deptoTxt);
                    $depto->setEdificio($edif);
                    $depto->setObservaciones('Localidad: ' . trim($imp->getLocalidad()));
                    $em->persist($depto);
                    $em->flush();
                }
                $ubicacion->setDepartamento($depto);
                // piso
                $piso = $em->getRepository('ConfigBundle:Piso')->find($imp->getPisoId());
                $ubicacion->setPiso($piso);
                $equipo->addUbicacion($ubicacion);
                $em->persist($equipo);
                $em->flush();
                echo ' - Creado ubicacion ';

                $em->getConnection()->commit();
                echo 'commit';
                echo '<br>';
            }
            catch (Exception $ex) {
                $em->getConnection()->rollback();
                break;
                echo 'rollback';
                var_dump($ex->getErrorCode());
            }
        }

        return null;
    }

    /*
     * BUSQUEDA DE EQUIPO GENERAL
     */

    /**
     * @Route("/buscarEquipo", name="buscar_equipo")
     * @Method("POST")
     * @Template()
     */
    public function buscarEquipoAction(Request $request) {
        $term = $request->get('search_term');
        $em = $this->getDoctrine()->getManager();
        $equipos = $em->getRepository('AppBundle:Equipo')->findEquipobyTerm($term);

        die;
        return false;
    }

    /**
     * @Route("/getAutocompleteEquipoTipo", name="get-autocomplete-equipo-tipo")
     * @Method("GET")
     */
    public function getAutocompleteEquipoTipo(Request $request) {
        $term = $request->get('q');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Tipo')->filterTipoEquipoByTerm($term);
        return new JsonResponse($results);
    }

    /**
     * @Route("/getAutocompleteEquipoMarca", name="get-autocomplete-equipo-marca")
     * @Method("GET")
     */
    public function getAutocompleteEquipoMarca(Request $request) {
        $term = $request->get('q');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Marca')->filterMarcaEquipoByTerm($term);
        return new JsonResponse($results);
    }

    /**
     * @Route("/getAutocompleteEquipoModelo", name="get-autocomplete-equipo-modelo")
     * @Method("GET")
     */
    public function getAutocompleteEquipoModelo(Request $request) {
        $term = $request->get('q');
        $marca = $request->get('marca');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Modelo')->filterModeloEquipoByTerm($term, $marca);
        return new JsonResponse($results);
    }

    /**
     * @Route("/getAutocompleteEquipoEstado", name="get-autocomplete-equipo-estado")
     * @Method("GET")
     */
    public function getAutocompleteEquipoEstado(Request $request) {
        $term = $request->get('q');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Estado')->filterEstadoEquipoByTerm($term);
        return new JsonResponse($results);
    }

    /**
     * @Route("/equipoListDatatables", name="equipo_list_datatables")
     * @Method("POST")
     * @Template()
     */
    public function equipoListDatatablesAction(Request $request) {
        // Set up required variables
        $this->entityManager = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $this->entityManager->getFilters()->disable('softdeleteable');
        }
        $this->repository = $this->entityManager->getRepository('AppBundle:Equipo');
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->request->get('draw'));
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');

            $session = $this->get('session');
            $sessionFiltro = $session->get('filtro_equipo');
        }
        else // If the request is not a POST one, die hard
            die;

        // Process Parameters
        // Orders
        /* foreach ($orders as $key => $order) {
          // Orders does not contain the name of the column, but its number,
          // so add the name so we can handle it just like the $columns array
          $orders[$key]['name'] = $columns[$order['column']]['name'];
          } */

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = "array or whatever is needed";
        // usuario para restringir edificios
        $userId = $this->getUser()->getId();

        // Get results from the Repository
        $results = $this->repository->getListDTData($start, $length, $orders, $search, $columns, $otherConditions = null, $sessionFiltro, $userId);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $this->repository->listcount($this->getUser()->getId());

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

        foreach ($objects as $key => $equipo) {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column) {
                // In all cases where something does not exist or went wrong, return -
                $responseTemp = "-";

                switch ($column['name']) {
                    case 'plus': {
                            $deleted = ($equipo->getDeletedAt()) ? 1 : 0;
                            $responseTemp = "<i class='fa fa-plus' data-deleted='" . $deleted . "'   data-id='" . $equipo->getId() . "'></i>";
                            break;
                        }
                    case 'tipo': {
                            $tipo = $equipo->getTipo();
                            // This cannot happen if inner join is used
                            // However it can happen if left or right joins are used
                            if ($tipo !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $tipo->getNombre()));
                            }
                            break;
                        }
                    case 'nroSerie': {
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $equipo->getNroSerie()));
                            break;
                        }
                    case 'nombre': {
                            $name = $equipo->getNombre();

                            // Do this kind of treatments if you suspect that the string is not JS compatible
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $name));
                            // View permission ?
                            /* if ($this->get('security.authorization_checker')->isGranted('view_town', $town))
                              {
                              // Get the ID
                              $id = $town->getId();
                              // Construct the route
                              $url = '';
                              //$this->generateUrl('playground_town_view', array('id' => $id));
                              // Construct the html code to send back to datatables
                              $responseTemp = "<a href='".$url."' target='_self'>".$ref."</a>";
                              }
                              else
                              {
                              $responseTemp = $name;
                              } */
                            break;
                        }
                    case 'marca': {
                            $marca = $equipo->getMarca();
                            if ($marca !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $marca->getNombre()));
                            }
                            break;
                        }
                    case 'modelo': {
                            $modelo = $equipo->getModelo();
                            if ($modelo !== null) {
                                $responseTemp = htmlentities(str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $modelo->getNombre()));
                            }
                            break;
                        }
                    case 'estado': {
                            $estado = $equipo->getEstado();
                            if ($estado !== null) {
                                $responseTemp = $estado->getNombre();
                            }
                            break;
                        }
                    case 'ubicacion': {
                            if ($equipo->getUbicacionActual() !== null) {
                                $responseTemp = $equipo->getUbicacionActual()->getTexto();
                            }
                            else {
                                $responseTemp = 'Sin ubicación';
                            }
                            break;
                        }
                    case 'verificado': {
                            if ($equipo->getVerificado()) {
                                $responseTemp = "<i class='fa fa-check-square-o'></i>";
                            }
                            else {
                                $responseTemp = "<i class='fa fa-square-o'></i>";
                            }
                            break;
                        }
                    case 'actions': {
                            $user = $this->getUser();
                            $responseTemp = '';
                            if ($user->getAccess('equipo_edit') or $user->getRol()->getAdmin()) {
                                $responseTemp = "<a href='" . $this->generateUrl('equipo_edit', array('id' => $equipo->getId())) . "' data-toggle='tooltip' title='Editar' ><i class='fa fa-edit'></i></a>";
                            }
                            $responseTemp = $responseTemp . "<a data-toggle='tooltip' title='Imprimir ficha del equipo' target='_blank' href='" . $this->generateUrl('print_ficha_equipo', array('id' => $equipo->getId())) . "'><i class='fa fa-print'></i></a>&nbsp;";
                            if ($user->getAccess('equipo_delete') or $user->getRol()->getAdmin()) {
                                $responseTemp = $responseTemp . "&nbsp;&nbsp;<a class='eliminar-equipo' href='" . $this->generateUrl('equipo_delete_ajax', array('id' => $equipo->getId())) . "' data-toggle='tooltip' title='Eliminar' ><i class='fa fa-trash-o'></i></a>&nbsp;";
                            }
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

    private function getTextoFiltro($em, $datos) {
        $cadena = '';
        if ($datos) {
            foreach ($datos as $i => $dato) {
                $texto = $em->getRepository('ConfigBundle:Tipo')->find($dato);
                $cadena = $cadena . $texto;
                if ($i < count($datos) - 1) {
                    $cadena = $cadena . ' - ';
                }
            }
        }
        return $cadena;
    }

    /**
     * PLANILLA VALORIZADO DE EQUIPOS
     */

    /**
     * @Route("/valorizado", name="equipo_valorizado")
     * @Method("GET")
     */
    public function valorizadoAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'equipo_valorizado');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_equipo_valorizado');

        switch ($request->get('_opFiltro')) {
            case 'limpiar':
                $filtro = array('tipoReporte' => 'detalle', 'selTipos' => NULL,
                    'idMarca' => 0, 'idModelo' => 0, 'idUbicacion' => 0, 'cotizacion' => 1);
                break;
            case 'buscar':
                $filtro = array(
                    'tipoReporte' => $request->get('tipoReporte'),
                    'selTipos' => $request->get('selTipos'),
                    'idMarca' => $request->get('idMarca'),
                    'idModelo' => $request->get('idModelo'),
                    'idUbicacion' => $request->get('idUbicacion'),
                    'cotizacion' => $request->get('cotizacion'),
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $filtro = array(
                        'tipoReporte' => $sessionFiltro['tipoReporte'],
                        'selTipos' => $sessionFiltro['selTipos'],
                        'idMarca' => $sessionFiltro['idMarca'],
                        'idModelo' => $sessionFiltro['idModelo'],
                        'idUbicacion' => $sessionFiltro['idUbicacion'],
                        'cotizacion' => $sessionFiltro['cotizacion'],
                    );
                }
                else {
                    $filtro = array('tipoReporte' => 'detalle', 'selTipos' => NULL,
                        'idMarca' => 0, 'idModelo' => 0, 'idUbicacion' => 0, 'cotizacion' => 1);
                }
                break;
        }

        $filtro['cotizacion'] = ($filtro['cotizacion'] == 0) ? 1 : $filtro['cotizacion'];
        $session->set('filtro_equipo_valorizado', $filtro);
        $userId = $this->getUser()->getId();

        $em->getFilters()->disable('softdeleteable');

        if ($filtro['tipoReporte'] == 'detalle') {
            // informe detallado de equipos valorizados
            $entities = $em->getRepository('AppBundle:Equipo')->findValorizadoByCriteria($filtro);
        }
        else {
            // informe sumariado por filtro: tipo - marca - modelo
            $entities = null;
        }

        $tipos = $em->getRepository('AppBundle:Equipo')->valorizadoCombosByCriteria($filtro, 'DISTINCT t.id,t.nombre', 't.nombre', 'tipo', $userId);

        $marcas = $em->getRepository('AppBundle:Equipo')->valorizadoCombosByCriteria($filtro, 'DISTINCT ma.id,ma.nombre', 'ma.nombre', 'marca', $userId);
        if ($filtro['idMarca']) {
            $modelos = $em->getRepository('AppBundle:Equipo')->valorizadoCombosByCriteria($filtro, 'DISTINCT mo.id,mo.nombre', 'mo.nombre', 'modelo', $userId);
        }
        else {
            $modelos = NULL;
        }

        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->findAll();

        return $this->render('AppBundle:Equipo:informe-valorizado.html.twig', array(
                    'entities' => $entities,
                    'tipos' => $tipos,
                    'marcas' => $marcas,
                    'modelos' => $modelos,
                    'ubicaciones' => $ubicaciones,
        ));
    }

    /**
     * @Route("/printValorizadoEquipos", name="print_equipo_valorizado")
     * @Method("POST")
     * @Template()
     */
    public function printValorizadoEquipos(Request $request) {

        $op = $request->get('option');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $filtro = $session->get('filtro_equipo_valorizado');
        $searchTerm = $request->get('searchterm');

        $tipos = $this->getTextoFiltro($em, $filtro['selTipos']);
        $marca = $em->getRepository('ConfigBundle:Marca')->find($filtro['idMarca']);
        $modelo = $em->getRepository('ConfigBundle:Modelo')->find($filtro['idModelo']);
        $ubicacion = $em->getRepository('ConfigBundle:Ubicacion')->find($filtro['idUbicacion']);

        $arrayFiltro = array(
            'tipo' => $tipos ? $tipos : 'Todos',
            'marca' => $marca ? $marca->getNombre() : 'Todas',
            'modelo' => $modelo ? $modelo->getNombre() : 'Todos',
            'ubicacion' => $ubicacion ? $ubicacion->getAbreviatura() : 'Todas',
            'cotizacion' => $filtro['cotizacion'],
            'tipoReporte' => $filtro['tipoReporte']);

        $hoy = new \DateTime();
        $userId = $this->getUser()->getId();
        $em->getFilters()->disable('softdeleteable');
        if ($filtro['tipoReporte'] == 'detalle') {
            // informe detallado de equipos valorizados
            $entities = $em->getRepository('AppBundle:Equipo')->findValorizadoByCriteria($filtro);
        }
        else {
            // informe sumariado por filtro: tipo - marca - modelo
            $entities = null;
        }
        switch ($op) {
            case 'pdf':
                $entities = $em->getRepository('AppBundle:Equipo')->findSqlByCriteria($filtro, 'RES', $searchTerm, $userId);
                $logo1 = __DIR__ . '/../../../web/bundles/app/img/pdf_logo.png';
                $facade = $this->get('ps_pdf.facade');
                $response = new Response();
                $this->render('AppBundle:Equipo:listado.pdf.twig', array('items' => $entities, 'filtro' => $arrayFiltro, 'logo' => $logo1,
                    'search' => $request->get('searchterm')), $response);

                $xml = $response->getContent();
                $content = $facade->render($xml);

                return new Response($content, 200, array('content-type' => 'application/pdf',
                    'Content-Disposition' => 'filename=listado_equipos_' . $hoy->format('dmY_Hi') . '.pdf'));

            case 'xls':
                $partial = $this->renderView('AppBundle:Equipo:valorizado-xls.html.twig',
                        array('items' => $entities, 'filtro' => $arrayFiltro));

                $fileName = 'Valorizado_Equipos_' . $hoy->format('dmY_Hi');
                $response = new Response();
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
                $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
                $response->setContent($partial);
                return $response;
        }
    }

    /**
     * @Route("/resetVerificado", name="equipo-reset-verificado")
     * @Method("GET")
     */
    public function resetVerificadoAction() {
        if (!$this->getUser()->getRol()->getAdmin()) {
            return new JsonResponse('ERROR');
        }
        try {
            $em = $this->getDoctrine()->getManager();
            $results = $em->getRepository('AppBundle:Equipo')->setNoVerificado();
            $msg = ($results) ? 'OK' : 'ERROR';
        }
        catch (Exception $ex) {
            $msg = 'ERROR';
        }
        return new Response($msg);
    }

}