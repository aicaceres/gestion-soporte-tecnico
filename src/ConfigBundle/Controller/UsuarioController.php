<?php

namespace ConfigBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Usuario;
use ConfigBundle\Form\UsuarioType;
use ConfigBundle\Form\ProfileType;

/**
 * @Route("/usuario")
 */
class UsuarioController extends Controller {
    /**
     *
     * LOGIN
     *
     */

    /**
     * @Route("/login", name="usuario_login")
     */
    public function loginAction() {
        $authUtils = $this->get('security.authentication_utils');
        return $this->render('::login.html.twig', array(
                    'appname' => 'Gestión de Inventario',
                    'last_username' => $authUtils->getLastUsername(),
                    'error' => $authUtils->getLastAuthenticationError(),
        ));
    }

    /**
     * @Route("/login_check", name="usuario_login_check")
     */
    public function loginCheckAction() {
        // el "login check" lo hace Symfony automáticamente
    }

    /**
     * @Route("/logout", name="usuario_logout")
     */
    public function logoutAction() {
        // el logout lo hace Symfony automáticamente
    }

    /**
     * @Route("/profile", name="usuario_profile")
     * @Method("GET")
     * @Template("ConfigBundle:Usuario:profile.html.twig")
     */
    public function profileAction() {
        $form = $this->createForm(new ProfileType(), $this->getUser(), array(
            'action' => $this->generateUrl('usuario_profile_update'),
            'method' => 'PUT',
        ));
        return array(
            'entity' => $this->getUser(),
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/profile_update", name="usuario_profile_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Usuario:profile.html.twig")
     */
    public function profileUpdateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getUser();
        $form = $this->createForm(new ProfileType(), $entity, array(
            'action' => $this->generateUrl('usuario_profile_update'),
            'method' => 'PUT',
        ));
        $passwordOriginal = $form->getData()->getPassword();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (null == $entity->getPassword()) {
                $entity->setPassword($passwordOriginal);
            }
            else {
                $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
                $entity->setPassword($password);
            }
            $em->flush();
            $this->addFlash('success', 'El perfil fue actualizado!');
            return $this->redirectToRoute('homepage');
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/findTecnicosHabilitados", name="find_tecnicos_habilitados")
     * @Method("GET")
     */
    public function findTecnicosHabilitados(Request $request) {
        $reqId = $request->get('requid');
        $em = $this->getDoctrine()->getManager();
        $tecnicos = $em->getRepository('ConfigBundle:Usuario')->findTecnicosHabilitados($reqId);
        return new JsonResponse($tecnicos);
    }

    /**
     * @Route("/", name="seguridad_usuario")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        UtilsController::haveAccess($this->getUser(), 'seguridad_usuario');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Usuario')->findAll();
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
     * @Route("/", name="seguridad_usuario_create")
     * @Method("PUT")
     * @Template("ConfigBundle:Usuario:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'seguridad_usuario');
        $entity = new Usuario();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
                $entity->setPassword($password);
                $entity->setNombre(strtoupper($entity->getNombre()));
                $em->persist($entity);
                $em->flush();
                $this->addFlash('success', 'El usuario fue creado!');
                return $this->redirectToRoute('seguridad_usuario');
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
     * @param Usuario $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Usuario $entity) {
        $form = $this->createForm(new UsuarioType(), $entity, array(
            'action' => $this->generateUrl('seguridad_usuario_create'),
            'method' => 'PUT',
        ));
        //$form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }

    /**
     * @Route("/new", name="seguridad_usuario_new")
     * @Method("GET")
     * @Template("ConfigBundle:Usuario:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), 'seguridad_usuario');
        $em = $this->getDoctrine()->getManager();
        $rol = $em->getRepository('ConfigBundle:Rol')->findOneByNombre('ROLE_USER');
        $entity = new Usuario();
        $entity->setRol($rol);
        $form = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/{id}", name="seguridad_usuario_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario.');
        }
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="seguridad_usuario_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), 'seguridad_usuario');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @param Usuario $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Usuario $entity) {
        $form = $this->createForm(new UsuarioType(), $entity, array(
            'action' => $this->generateUrl('seguridad_usuario_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        // $form->add('submit', 'submit', array('label' => 'Update'));
        return $form;
    }

    /**
     * @Route("/update/{id}", name="seguridad_usuario_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Usuario:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'seguridad_usuario');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $passwordOriginal = $editForm->getData()->getPassword();
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            try {
                if (null == $entity->getPassword()) {
                    $entity->setPassword($passwordOriginal);
                }
                else {
                    $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                    $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
                    $entity->setPassword($password);
                }
                $em->flush();
                $this->addFlash('success', 'Modificado con éxito!');
                return $this->redirectToRoute('seguridad_usuario');
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @Route("/delete/{id}", name="seguridad_usuario_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'seguridad_usuario_delete');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('No existe el usuario.');
            }
            try {
                $em->remove($entity);
                $em->flush();
                $this->addFlash('success', 'Se ha eliminado con éxito!');
                return $this->redirectToRoute('seguridad_usuario');
            }
            catch (\Exception $ex) {
                $this->addFlash('danger', UtilsController::errorMessage($ex->getErrorCode()));
            }
        }
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('seguridad_usuario_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

}