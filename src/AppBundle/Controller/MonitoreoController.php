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
use AppBundle\Entity\Vencimiento;
use AppBundle\Form\VencimientoType;

class MonitoreoController extends Controller {

    /**
     * @Route("/monitoreo", name="monitoreo_estadored")
     * @Method("GET")
     * @Template("AppBundle:Monitoreo:index.html.twig")
     */
    public function estadoAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_estadored');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_monitoreo');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $filtro = array(
                    'selUbicaciones' => $request->get('selUbicaciones'),
                    'selTipo' => $request->get('selTipo'),
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $filtro = array(
                        'selUbicaciones' => $sessionFiltro['selUbicaciones'],
                        'selTipo' => $sessionFiltro['selTipo']
                    );
                }
                else {
                    $filtro = array('selUbicaciones' => [], 'selTipo' => 0);
                }
                break;
        }
        $session->set('filtro_monitoreo', $filtro);
        $entities = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesParaMonitoreo();
        $ubicaciones = array();
        $reclamosAbiertos = 0;
        if ($filtro['selUbicaciones']) {
            foreach ($entities as $ent) {
                if (in_array($ent->getId(), $filtro['selUbicaciones'])) {
                    /* $estado = ['danger'=>0,'warning'=>0,'success'=>0];
                      foreach( $ent->getEdificios() as $edif ){
                      foreach ($edif->getDepartamentos() as $dep){
                      if( $dep->getIpPrincipal() ){
                      $estado[UtilsController::checkIP( $dep->getIpPrincipal(),1 )['estado']] += 1 ;
                      }
                      if( $dep->getIpRespaldo() ){
                      $estado[UtilsController::checkIP( $dep->getIpRespaldo(),1 )['estado']] += 1 ;
                      }
                      if( $dep->getProveedor() ){
                      $reclamosAbiertos += $dep->getProveedor()->getReclamosAbiertos();
                      }
                      }
                      } */
                    $ubicaciones[] = array('id' => $ent->getId(), 'nombre' => $ent->getAbreviatura(), 'estado' => null);
                }
            }
        }
        $tipos = $em->getRepository('ConfigBundle:Tipo')->findBy(array('clase' => 'E'), array('nombre' => 'ASC'));
        return array(
            'ubicaciones' => $ubicaciones,
            'tipos' => $tipos,
            'ubiclist' => $entities,
            'reclamos' => $reclamosAbiertos
        );
    }

    /**
     * @Route("/monitoreoIpDepartamento/{id}", name="monitoreo_ip_departamento")
     * @Method("GET")
     */
    public function monitoreoIpDepartamentoAction($id) {
        $em = $this->getDoctrine()->getManager();
        $dep = $em->getRepository('ConfigBundle:Departamento')->find($id);
        $ip1 = UtilsController::checkIP($dep->getIpPrincipal(), 1);
        $ip2 = UtilsController::checkIP($dep->getIpRespaldo(), 1);
        $salida1 = ( ($dep->getIpPrincipal()) ? $ip1['text'] : '' );
        $salida2 = ( ($dep->getIpRespaldo()) ? $ip2['text'] : '' );
        return new JsonResponse($salida1 . chr(13) . chr(13) . $salida2);
    }

    /**
     * @Route("/testIP", name="monitoreo_testip")
     * @Method("GET")
     */
    public function testIPAction(Request $request) {
        $ip = $request->get('ip');
        $n = ($request->get('intentos')) ? $request->get('intentos') : 1;
        $resp = UtilsController::checkIP($ip, ($n) ? $n : 1);
        $salidaPing = str_replace(chr(161), "í", $resp['salidaPing']);
        $salidaPing = str_replace(chr(160), "á", $salidaPing);
        $partial = $this->renderView('AppBundle:Monitoreo:partial-testip.html.twig',
                array('salidaPing' => $salidaPing, 'intentos' => $n, 'respuesta' => $resp));
        return new Response($partial);
    }

    /**
     * @Route("/testUbicIP", name="monitoreo_testUbicacionip")
     * @Method("GET")
     */
    public function testUbicacionIPAction(Request $request) {
        // Retornar semaforo para la ubicacion
        $em = $this->getDoctrine()->getManager();
        $ips = $em->getRepository('ConfigBundle:Ubicacion')->getIpsParaMonitoreo($request->get('ubic'));
        /* $semaforo = array('green'=>0,'yellow2'=>0,'red'=>0);
          foreach ($ips as $ip) {
          $resp = UtilsController::checkIP( $ip['ip'],1 );
          $bg = split('-', $resp['bg']) ;
          $semaforo[ $bg[1] ] += 1;
          } */
        return new JsonResponse($ips);
    }

    /**
     * @Route("/getMonitoreoEdificios/{ubicid}", name="get_monitoreo_edificios")
     * @Method("GET")
     */
    public function getMonitoreoEdificiosAction($ubicid) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Ubicacion')->getEdificiosParaMonitoreo($ubicid);
        $html = '';
        foreach ($entities as $ent) {
            /* $estado = ['danger'=>0,'warning'=>0,'success'=>0];
              foreach ($ent->getDepartamentos() as $dep){
              if( $dep->getIpPrincipal() ){
              $estado[UtilsController::checkIP( $dep->getIpPrincipal(),1 )['estado']] += 1 ;
              }
              if( $dep->getIpRespaldo() ){
              $estado[UtilsController::checkIP( $dep->getIpRespaldo(),1 )['estado']] += 1 ;
              }
              } */
            $html = $html . $this->renderView('AppBundle:Monitoreo:partial-edificio.html.twig',
                            array('data' => array('ubicacion' => $ubicid, 'id' => $ent->getId(), 'nombre' => $ent->getNombre(), 'estado' => null))
            );
        }
        return new Response($html);
    }

    /**
     * @Route("/getMonitoreoDepartamentos/{edifid}", name="get_monitoreo_departamentos")
     * @Method("GET")
     */
    public function getMonitoreoDepartamentosAction($edifid) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Ubicacion')->getDepartamentosParaMonitoreo($edifid);
        $html = '';
        $bg = 'bg-success';
        foreach ($entities as $dep) {
            if ($dep->getIpPrincipal()) {
                //$estado['principal'] = UtilsController::checkIP($dep->getIpPrincipal(), 1);
                $estado['principal']['estado'] = NULL;
            }
            if ($dep->getIpRespaldo()) {
                //$estado['respaldo'] = UtilsController::checkIP($dep->getIpRespaldo(), 1);
                $estado['respaldo']['estado'] = NULL;
            }
            else {
                $estado['respaldo']['estado'] = $estado['principal']['estado'];
            }
            /* if( $estado['principal']['estado']=='danger' && $estado['respaldo']['estado']=='danger' ){
              $bg = 'bg-danger';
              }elseif ($estado['principal']['estado']=='success' && $estado['respaldo']['estado']=='success') {
              $bg = 'bg-success';
              }else{
              $bg = 'bg-warning';
              } */
            $html = $html . $this->renderView('AppBundle:Monitoreo:partial-departamento.html.twig',
                            array('departamento' => $dep, 'monitoreo' => null, 'bg' => 'bg-default')
            );
        }
        return new Response($html);
    }

    /**
     * @Route("/getMonitoreoEquipos/{dptoid}", name="get_monitoreo_equipos")
     * @Method("GET")
     */
    public function getMonitoreoEquiposAction(Request $request, $dptoid) {
        $em = $this->getDoctrine()->getManager();
        $equipos = $em->getRepository('AppBundle:Equipo')->getEquiposParaMonitoreo($dptoid);
        $departamento = $em->getRepository('ConfigBundle:Departamento')->find($dptoid);
        $padre = 'U' . $departamento->getEdificio()->getUbicacion()->getId() . 'E' . $departamento->getEdificio()->getId() . 'D' . $departamento->getId();
        $html = '';
        $tipo = $request->get('tipo');
        foreach ($equipos as $equipo) {
            $ip = $equipo->getUbicacionActual()->getRedIp();
            if (($tipo && $equipo->getTipo()->getId() == $tipo) || !$tipo) {
                $estado = UtilsController::checkIP($ip, 1);
            }
            else {
                $estado = array('text' => '', 'estado' => 'default', 'bg' => 'default',
                    'paquetesRecibidos' => 0, 'tiempo' => 0, 'resultado' => '', 'salidaPing' => '');
            }
            $html = $html . $this->renderView('AppBundle:Monitoreo:partial-equipo.html.twig',
                            array('equipo' => $equipo, 'monitoreo' => $estado, 'ip' => $ip, 'padre' => $padre)
            );
        }
        return new Response($html);
    }

    /*     * *
     * VENCIMIENTOS
     */

    /**
     * @Route("/vencimiento", name="monitoreo_vencimiento")
     * @Method("GET")
     * @Template()
     */
    public function vencimientoAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_vencimiento');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $data = array('tipoId' => $request->get('tipoId'), 'proveedorId' => $request->get('proveedorId'),
            'estado' => $request->get('estado'), 'desde' => $request->get('desde'), 'hasta' => $request->get('hasta'));
        $entities = $em->getRepository('AppBundle:Vencimiento')->findByCriteria($data);
        $tipos = $em->getRepository('ConfigBundle:TipoVencimiento')->findAll();
        $proveedores = $em->getRepository('AppBundle:Proveedor')->findAll();
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }
        return array(
            'entities' => $entities,
            'tipos' => $tipos,
            'proveedores' => $proveedores,
            'data' => $data,
            'deleteForms' => $deleteForms
        );
    }

    /**
     * @Route("/vencimiento/new", name="monitoreo_vencimiento_new")
     * @Method("GET")
     * @Template("AppBundle:Monitoreo:vencimiento-edit.html.twig")
     */
    public function newAction() {
        $entity = new Vencimiento();
        $form = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @param Compra $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Vencimiento $entity) {
        $form = $this->createForm(new VencimientoType(), $entity, array(
            'action' => $this->generateUrl('monitoreo_vencimiento_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/vencimiento", name="monitoreo_vencimiento_create")
     * @Method("PUT")
     * @Template("AppBundle:Monitoreo:vencimiento-edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_vencimiento_new');
        $data = $request->get('appbundle_vencimiento');
        $em = $this->getDoctrine()->getManager();
        $new = $data['savenew'];
        $entity = new Vencimiento();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $em->getConnection()->beginTransaction();
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();

                if ($new == 'S') {
                    $this->addFlash('success', 'El vencimiento fue creado. Puede crear el siguiente!');
                    return $this->redirectToRoute('monitoreo_vencimiento_new');
                }
                else {
                    $this->addFlash('success', 'El vencimiento fue creado!');
                    return $this->redirectToRoute('monitoreo_vencimiento');
                }
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', $ex->getMessage());
            }
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/vencimiento/{id}/edit", name="monitoreo_vencimiento_edit")
     * @Method("GET")
     * @Template("AppBundle:Monitoreo:vencimiento-edit.html.twig")
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_vencimiento_edit');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Vencimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el vencimiento.');
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
    private function createEditForm(Vencimiento $entity) {
        $form = $this->createForm(new VencimientoType(), $entity, array(
            'action' => $this->generateUrl('monitoreo_vencimiento_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/vencimiento/{id}", name="monitoreo_vencimiento_update")
     * @Method("PUT")
     * @Template("AppBundle:Monitoreo:vencimiento-edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $entity = $em->getRepository('AppBundle:Vencimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el vencimiento.');
        }
        $data = $request->get('appbundle_vencimiento');
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirectToRoute('monitoreo_vencimiento');
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
                        ->setAction($this->generateUrl('monitoreo_vencimiento_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * @Route("/vencimiento/delete/{id}", name="monitoreo_vencimiento_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_vencimiento_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity = $em->getRepository('AppBundle:Vencimiento')->find($id);
                if (!$entity) {
                    throw $this->createNotFoundException('No existe el vencimiento.');
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
                $this->addFlash('success', 'El vencimiento fue eliminado!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $this->addFlash('danger', $ex->getMessage());
            }
        }

        return $this->redirectToRoute('monitoreo_vencimiento');
    }

    /**
     * @Route("/vencimiento/{id}/show", name="monitoreo_vencimiento_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), 'monitoreo_vencimiento');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository('AppBundle:Vencimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el vencimiento.');
        }
        $html = $this->renderView('AppBundle:Monitoreo:vencimiento-show.html.twig',
                array('entity' => $entity));
        return new Response($html);
    }

    /**
     * @Route("/getAlertaVencimientos", name="get_alerta_vencimientos")
     * @Method("GET")
     */
    public function getAlertaVencimientos() {
        $em = $this->getDoctrine()->getManager();
        $vencimientos = $em->getRepository('AppBundle:Vencimiento')->findAlertas();
        $cantidad = array('warning' => 0, 'danger' => 0, 'success' => 0);
        foreach ($vencimientos as $venc) {
            ++$cantidad[$venc->getEstado()];
        }
        $partial = $this->renderView('AppBundle:Monitoreo:partial-alertas.html.twig',
                array('vencimientos' => $vencimientos));
        return new Response(json_encode(array('partial' => $partial, 'cantidad' => $cantidad)));
    }

}