<?php

namespace ConfigBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Ubicacion;
use ConfigBundle\Entity\Departamento;
use ConfigBundle\Entity\Edificio;
use ConfigBundle\Form\UbicacionType;
use Symfony\Component\Process\Process;

/**
 * @Route("/ubicacion")
 */
class UbicacionController extends Controller {
    // prueba de ping

    /**
     * @Route("/pruebaping", name="pruebaping")
     * @Method("GET")
     */
    public function pruebaping(Request $request) {
        $ip = $request->get('ip');
        $resp = UtilsController::checkIP($ip, 1);
        return new JsonResponse(array('text' => $resp['text'], 'bg' => $resp['bg']));
    }

    private $tableDesc = array('Ubicacion' => 'Ubicacion', 'Departamento' => 'Departamentos',
        'Edificio' => 'Edificios');

    /*
     * Functions
     */

    private function getNewObject($obj) {
        switch ($obj) {
            case 'Ubicacion':
                $entity = new Ubicacion();
                break;
            case 'Departamento':
                $entity = new Departamento();
                break;
            case 'Edificio':
                $entity = new Edificio();
                break;
        }
        return $entity;
    }

    /**
     * @Route("/{table}", name="configuracion_ubicacion")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request, $table) {
        UtilsController::haveAccess($this->getUser(), 'configuracion_ubicacion');
        $em = $this->getDoctrine()->getManager();
        /* if ($this->getUser()->getRol()->getAdmin()) {
          $em->getFilters()->disable('softdeleteable');
          } */
        $ubicaciones = $edificios = NULL;
        if ($table == 'Departamento') {
            $session = $this->get('session');
            $sessionFiltro = $session->get('filtro_ubicacion');
            switch ($request->get('_opFiltro')) {
                case 'buscar':
                    $filtro = array(
                        'idUbicacion' => $request->get('idUbicacion'),
                        'idEdificio' => $request->get('idEdificio'),
                    );
                    break;
                default:
                    //desde paginacion, se usa session
                    if ($sessionFiltro) {
                        $filtro = array(
                            'idUbicacion' => $sessionFiltro['idUbicacion'],
                            'idEdificio' => $sessionFiltro['idEdificio'],
                        );
                    }
                    else {
                        $filtro = array('idUbicacion' => 0, 'idEdificio' => 0);
                    }
                    break;
            }

            $session->set('filtro_ubicacion', $filtro);
            $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->findAll();
            $edificios = NULL;
            if ($filtro['idUbicacion']) {
                $edificios = $em->getRepository('ConfigBundle:Edificio')->findByUbicacionId($filtro['idUbicacion']);
            }
            $entities = $em->getRepository('ConfigBundle:Departamento')->findDepartamentoByCriteria($filtro);
        }
        else {
            $entities = $em->getRepository('ConfigBundle:' . $table)->findAll();
        }

        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($table, $entity->getId())->createView();
        }
        $html = $this->renderView('ConfigBundle:Ubicacion:list.html.twig', array('entities' => $entities, 'table' => $table, 'deleteForms' => $deleteForms,
            'ubicaciones' => $ubicaciones, 'edificios' => $edificios,)
        );
        return $this->render('ConfigBundle:Ubicacion:index.html.twig', array(
                    'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));
    }

    /**
     * @Route("/new/{table}", name="configuracion_ubicacion_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($table) {
        UtilsController::haveAccess($this->getUser(), 'configuracion_ubicacion');
        $entity = $this->getNewObject($table);
        if ($table == 'Departamento') {
            $em = $this->getDoctrine()->getManager();
            $localidad = $em->getRepository('ConfigBundle:Localidad')->findOneByByDefault(1);
            $entity->setLocalidad($localidad);
        }
        $urlCreate = $this->generateUrl('configuracion_ubicacion_create', array('table' => $table));
        $form = $this->createForm(new UbicacionType(), $entity, array(
            'action' => $urlCreate, 'method' => 'PUT',
        ));

        $html = $this->renderView('ConfigBundle:Ubicacion:edit.html.twig',
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table)
        );
        return new Response($html);
        /* return $this->render('ConfigBundle:Parametro:index.html.twig', array(
          'title' => $this->tableDesc[$table], 'html' => json_encode($html)
          )); */
    }

    /**
     * @Route("/create/{table}", name="configuracion_ubicacion_create")
     * @Method("PUT")
     * @Template()
     */
    public function createAction(Request $request, $table) {
        UtilsController::haveAccess($this->getUser(), 'configuracion_ubicacion');
        $entity = $this->getNewObject($table);
        $urlCreate = $this->generateUrl('configuracion_ubicacion_create', array('table' => $table));
        $form = $this->createForm(new UbicacionType(), $entity, array(
            'action' => $urlCreate, 'method' => 'PUT',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($entity);
                $em->flush();
                $this->addFlash('success', 'Creado con éxito!');
                return $this->redirectToRoute('configuracion_ubicacion', array('table' => $table));
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        $html = $this->renderView('ConfigBundle:Ubicacion:edit.html.twig',
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table)
        );
        return $this->render('ConfigBundle:Ubicacion:index.html.twig', array(
                    'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));
    }

    /**
     * @Route("/{table}/{id}/edit", name="configuracion_ubicacion_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($table, $id) {
        UtilsController::haveAccess($this->getUser(), 'configuracion_ubicacion');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('ConfigBundle:' . $table)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe');
        }
        $urlUpdate = $this->generateUrl('configuracion_ubicacion_update',
                array('table' => $table, 'id' => $entity->getId()));

        $form = $this->createForm(new UbicacionType(), $entity, array(
            'action' => $urlUpdate,
            'method' => 'PUT',
        ));
        $deleteForm = $this->createDeleteForm($table, $id);
        $html = $this->renderView('ConfigBundle:Ubicacion:edit.html.twig',
                array('entity' => $entity, 'form' => $form->createView(),
                    'table' => $table, 'delete_form' => $deleteForm->createView())
        );
        return new Response($html);
        /* return $this->render('ConfigBundle:Parametro:index.html.twig', array(
          'title' => $this->tableDesc[$table], 'html' => json_encode($html)
          )); */
    }

    /**
     * @Route("/update/{table}/{id}", name="configuracion_ubicacion_update")
     * @Method("PUT")
     * @Template()
     */
    public function updateAction(Request $request, $table, $id) {
        UtilsController::haveAccess($this->getUser(), 'configuracion_ubicacion');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('ConfigBundle:' . $table)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe.');
        }
        /* $original = new ArrayCollection();
          foreach ($entity->getModelos() as $item) {
          $original->add($item);
          } */
        $urlUpdate = $this->generateUrl('configuracion_ubicacion_update', array('table' => $table, 'id' => $entity->getId()));

        $form = $this->createForm(new UbicacionType(), $entity, array(
            'action' => $urlUpdate,
            'method' => 'PUT',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                /* /* remove the relationship
                  foreach ($original as $item) {
                  if (false === $entity->getModelos()->contains($item)) {
                  $em->remove($item);
                  }
                  } */
                $em->persist($entity);
                $em->flush();
                $this->addFlash('success', 'Modificado con éxito!');
                return $this->redirectToRoute('configuracion_ubicacion', array('table' => $table));
            }
            catch (\Exception $ex) {

                $this->addFlash('danger', $ex->getMessage());
                //$this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) );
            }
        }
        $deleteForm = $this->createDeleteForm($table, $id);
        $html = $this->renderView('ConfigBundle:Ubicacion:edit.html.twig',
                array('entity' => $entity, 'form' => $form->createView(),
                    'table' => $table, 'delete_form' => $deleteForm->createView())
        );
        /* $html = $this->renderView('ConfigBundle:Ubicacion:edit.html.twig',
          array('entity' => $entity, 'form' => $form->createView(), 'table' => $table  )
          ); */
        return $this->render('ConfigBundle:Ubicacion:index.html.twig', array(
                    'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));
    }

    /**
     * @Route("/delete/{table}/{id}", name="configuracion_ubicacion_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $table, $id) {
        UtilsController::haveAccess($this->getUser(), 'configuracion_ubicacion');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('ConfigBundle:' . $table)->find($id);
        try {
            if (is_null($entity->getDeletedAt())) {
                //forzar el guardado de ultima fecha de modificación antes de softdelete
                $em->getFilters()->enable('softdeleteable');
                $entity->setUpdated(new \DateTime());
                $em->persist($entity);
                $em->flush();
            }
            $em->remove($entity);
            $em->flush();
            $this->addFlash('success', 'Se ha eliminado con éxito!');
        }
        catch (\Exception $ex) {
            $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
        }
        return $this->redirectToRoute('configuracion_ubicacion', array('table' => $table));
    }

    private function createDeleteForm($table, $id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('configuracion_ubicacion_delete', array('table' => $table, 'id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * @Route("/selectEdificios", name="select_edificios")
     * @Method("POST")
     */
    public function edificiosAction(Request $request) {
        $ubicacion_id = $request->request->get('ubicacion_id');
        $em = $this->getDoctrine()->getManager();
        $edificios = $em->getRepository('ConfigBundle:Ubicacion')->findByUbicacionId($ubicacion_id, $this->getUser()->getId());
        return new JsonResponse($edificios);
    }

    /**
     * @Route("/selectDepartamentos", name="select_departamentos")
     * @Method("POST")
     */
    public function departamentosAction(Request $request) {
        $edificio_id = $request->request->get('edificio_id');
        $em = $this->getDoctrine()->getManager();
        $departamentos = $em->getRepository('ConfigBundle:Ubicacion')->findByEdificioId($edificio_id);
        return new JsonResponse($departamentos);
    }

    /**
     * @Route("/datosDepartamento", name="datos_departamento")
     * @Method("POST")
     */
    public function datosDepartamentoAction(Request $request) {
        $dpto_id = $request->request->get('depto_id');
        $em = $this->getDoctrine()->getManager();
        $departamento = $em->getRepository('ConfigBundle:Departamento')->find($dpto_id);
        $pisos = $em->getRepository('ConfigBundle:Ubicacion')->getArrayPisos($dpto_id);
        $html = $this->renderView('ConfigBundle:Ubicacion:partial-datos-departamento.html.twig',
                array('datos' => $departamento)
        );
        return new JsonResponse(array('html' => $html, 'pisos' => $pisos));
    }

    /**
     * @Route("/selectSolicitantes", name="select_solicitantes")
     * @Method("POST")
     */
    public function selectSolicitantesAction(Request $request) {
        $ubicId = $request->request->get('ubic_id');
        $em = $this->getDoctrine()->getManager();
        $deptos = $em->getRepository('ConfigBundle:Ubicacion')->findDptosByUbicacionId($ubicId);
        return new JsonResponse($deptos);
    }

    /**
     * @Route("/partial/renderSelectUbicacion", name="render_select_ubicacion")
     * @Method("GET")
     */
    public function renderSelectUbicacionAction() {
        $ubic = new \AppBundle\Entity\EquipoUbicacion();
        $ubicform = $this->createForm(new \AppBundle\Form\EquipoUbicacionType(), $ubic, array(
            'action' => '#',
            'method' => 'POST',
        ));
        $partial = $this->renderView('ConfigBundle:Ubicacion:partial-select-ubicacion.html.twig',
                array('ubicform' => $ubicform->createView()));
        return new Response($partial);
    }

    /**
     * @Route("/selEdificios", name="selEdificios")
     * @Method("POST")
     */
    public function selEdificiosAction(Request $request) {
        $ubicaciones = $request->get('ubicaciones_id');
        $em = $this->getDoctrine()->getManager();
        $edificios = $em->getRepository('ConfigBundle:Ubicacion')->findEdificiosByUbicaciones($ubicaciones, $this->getUser()->getId(), 'array');
        return new JsonResponse($edificios);
    }

    /**
     * @Route("/selDepartamentos", name="selDepartamentos")
     * @Method("POST")
     */
    public function selDepartamentosAction(Request $request) {
        $edificios = $request->get('edificios_id');
        $em = $this->getDoctrine()->getManager();
        $departamentos = $em->getRepository('ConfigBundle:Ubicacion')->findDepartamentosByEdificios($edificios, 'array');
        return new JsonResponse($departamentos);
    }

}