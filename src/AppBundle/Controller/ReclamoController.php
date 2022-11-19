<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\Reclamo;
use AppBundle\Form\ReclamoType;

/**
 * @Route("/reclamo")
 */
class ReclamoController extends Controller {

    /**
     * @Route("/", name="reclamo")
     * @Method("GET")
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $provid = $request->get('provid');
        $em = $this->getDoctrine()->getManager();
        $proveedor = $em->getRepository('ConfigBundle:DepartamentoProveedor')->find($provid);

        $partial = $this->renderView('AppBundle:Reclamo:index.html.twig',
                array('proveedor' => $proveedor));
        return new Response($partial);
    }

    /**
     * @Route("/{provid}/new", name="reclamo_new")
     * @Method("GET")
     * @Template("AppBundle:Reclamo:partial-new.html.twig")
     */
    public function newAction($provid) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $em = $this->getDoctrine()->getManager();
        $proveedor = $em->getRepository('ConfigBundle:DepartamentoProveedor')->find($provid);
        $entity = new Reclamo();
        $entity->setFecha(new \DateTime);
        $entity->setProveedor($proveedor);

        $form = $this->createCreateForm($entity, $proveedor);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    private function createCreateForm(Reclamo $entity, $proveedor) {
        $form = $this->createForm(new ReclamoType(), $entity, array(
            'action' => $this->generateUrl('reclamo_create'),
            'method' => 'PUT',
        ));
        $choice = array();
        if ($proveedor->getEnlaceProveedor()) {
            $choice['E'] = $proveedor->getEnlaceProveedor() . ' - ' . $proveedor->getEnlaceTipoConexion();
        }
        if ($proveedor->getInternetProveedor()) {
            $choice['I'] = $proveedor->getInternetProveedor() . ' - ' . $proveedor->getInternetTipoConexion();
        }
        if ($choice) {
            $form->add('tipoProveedor', 'choice', array('label' => 'Proveedor:', 'choices' => $choice));
        }

        return $form;
    }

    /**
     * @Route("/", name="reclamo_create")
     * @Method("PUT")
     * @Template("AppBundle:Reclamo:partial-new.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $entity = new Reclamo();

        $data = $request->get('appbundle_reclamo');
        $em = $this->getDoctrine()->getManager();
        $proveedor = $em->getRepository('ConfigBundle:DepartamentoProveedor')->find($data['proveedor']);

        $form = $this->createCreateForm($entity, $proveedor);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $hoy = new \DateTime();
                $detalle[] = array('fecha' => $hoy->format('d-m-Y H:i'),
                    'detalle' => $entity->getDetalle(),
                    'nroReferencia' => $entity->getNroReferencia(),
                    'referente' => $entity->getReferente(),
                    'usuario' => $this->getUser()->getUsername());
                $entity->setResumen(json_encode($detalle));
                $em->persist($entity);
                $em->flush();
                $proveedor = $em->getRepository('ConfigBundle:DepartamentoProveedor')->find($entity->getProveedor()->getId());
                $partial = $this->renderView('AppBundle:Reclamo:index.html.twig',
                        array('proveedor' => $proveedor));
                return new JsonResponse(array('msg' => 'OK', 'partial' => $partial));
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', $ex->getMessage());
            }
        }
        $partial = $this->renderView('AppBundle:Reclamo:partial-new.html.twig',
                array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
        $response = new JsonResponse(array('msg' => 'ERROR', 'form' => $partial));
        return $response;
    }

    /**
     * @Route("/{id}/edit", name="reclamo_edit")
     * @Method("GET")
     * @Template("AppBundle:Reclamo:partial-edit.html.twig")
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Reclamo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el reclamo.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        $arrayResumen = json_decode($entity->getResumen());

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'resumen' => $arrayResumen
        );
    }

    private function createEditForm(Reclamo $entity) {
        $form = $this->createForm(new ReclamoType(), $entity, array(
            'action' => $this->generateUrl('reclamo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="reclamo_update")
     * @Method("PUT")
     * @Template("AppBundle:Reclamo:partial-new.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Reclamo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el reclamo.');
        }
        $nro = $entity->getNroReferencia();
        $ref = $entity->getReferente();
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            try {
                $hoy = new \DateTime();
                $auxiliar = array('fecha' => $hoy->format('d-m-Y H:i'),
                    'detalle' => $request->get('appbundle_reclamo')['auxiliar'],
                    'nroReferencia' => $entity->getNroReferencia(),
                    'referente' => $entity->getReferente(),
                    'usuario' => $this->getUser()->getUsername());
                $arrayResumen = json_decode($entity->getResumen());
                array_push($arrayResumen, $auxiliar);
                $entity->setResumen(json_encode($arrayResumen));
                $entity->setNroReferencia($nro);
                $entity->setReferente($ref);
                $em->flush();
                $proveedor = $em->getRepository('ConfigBundle:DepartamentoProveedor')->find($entity->getProveedor()->getId());
                $partial = $this->renderView('AppBundle:Reclamo:index.html.twig',
                        array('proveedor' => $proveedor));
                return new JsonResponse(array('msg' => 'OK', 'partial' => $partial));
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        $deleteForm = $this->createDeleteForm($id);
        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'resumen' => $arrayResumen
        );
    }

    /**
     * @Route("/delete/{id}", name="reclamo_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {

    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('reclamo_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * @Route("/{id}/show", name="reclamo_show")
     * @Method("GET")
     * @Template("AppBundle:Reclamo:partial-show.html.twig")
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Reclamo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el reclamo.');
        }
        $arrayResumen = json_decode($entity->getResumen());

        return array(
            'entity' => $entity,
            'resumen' => $arrayResumen
        );
    }

    /**
     * @Route("/{id}/view", name="reclamo_view")
     * @Method("GET")
     * @Template("AppBundle:Reclamo:show.html.twig")
     */
    public function viewAction($id) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Reclamo')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el reclamo.');
        }
        $arrayResumen = json_decode($entity->getResumen());

        return array(
            'reclamo' => $entity,
            'resumen' => $arrayResumen
        );
    }

    /**
     * CRUD RECLAMOS
     */

    /**
     * @Route("/list", name="monitoreo_reclamo")
     * @Method("GET")
     * @Template("AppBundle:Reclamo:list.html.twig")
     */
    public function listAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_reclamo');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_reclamo');

        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
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
                        'estado' => $sessionFiltro['estado'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('estado' => 'T', 'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_reclamo', $filtro);

        $reclamos = $em->getRepository('AppBundle:Reclamo')->findByCriteria($filtro);
        return $this->render('AppBundle:Reclamo:list.html.twig', array(
                    'reclamos' => $reclamos,
                    'filtro' => $filtro
        ));
    }

}