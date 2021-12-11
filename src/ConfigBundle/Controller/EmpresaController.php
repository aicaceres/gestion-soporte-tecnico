<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Empresa;
use ConfigBundle\Form\EmpresaType;

/**
 * @Route("/empresa")
 */
class EmpresaController extends Controller
{
    /**
     * @Route("/", name="seguridad_empresa")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(),'seguridad_empresa');
        $em = $this->getDoctrine()->getManager();
        $empresa = $em->getRepository('ConfigBundle:Empresa')->find( 1 ); 
        if (!$empresa) {
            throw $this->createNotFoundException('Unable to find Empresa entity.');
        }
        $editForm = $this->createEditForm($empresa);
        return $this->render('ConfigBundle:Empresa:edit.html.twig', array(
            'entity'      => $empresa,
            'form'   => $editForm->createView(),
        ));
    }

    /**
    * @param Permiso $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Empresa $entity)
    {
        $form = $this->createForm(new EmpresaType(), $entity, array(
            'action' => $this->generateUrl('seguridad_empresa_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    
    /**
     * @Route("/{id}", name="seguridad_empresa_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Empresa:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'seguridad_empresa');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Empresa')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Empresa entity.');
        }       

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {            
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'Datos modificados con éxito!');
        }
        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
        );
    }

}