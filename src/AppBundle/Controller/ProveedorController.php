<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;

use AppBundle\Entity\Proveedor;
use AppBundle\Form\ProveedorType;

/**
 * @Route("/proveedor")
 */
class ProveedorController extends Controller
{
    /**
     * @Route("/", name="proveedor")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(),'proveedor');
        $em = $this->getDoctrine()->getManager();
        if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }
        $entities = $em->getRepository('AppBundle:Proveedor')->findAll();
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
     * @Route("/", name="proveedor_create")
     * @Method("PUT")
     * @Template("AppBundle:Proveedor:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(),'proveedor_new');
        $data = $request->get('appbundle_proveedor');
        $new = $data['savenew'];
        $entity = new Proveedor();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();            
            try {
                $em->persist($entity);
                $em->flush();                
                if ($new == 'S') {
                    $this->addFlash('success', 'El proveedor fue creado. Puede crear el siguiente!');
                    return $this->redirectToRoute('proveedor_new');
                } else {
                    $this->addFlash('success', 'El proveedor fue creado!');
                    return $this->redirectToRoute('proveedor');
                }
            } catch (\Exception $ex) {                
                $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
            }
        }
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * @param Proveedor $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Proveedor $entity)
    {
        $form = $this->createForm(new ProveedorType(), $entity, array(
            'action' => $this->generateUrl('proveedor_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="proveedor_new")
     * @Method("GET")
     * @Template("AppBundle:Proveedor:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(),'proveedor_new');
        $entity = new Proveedor();
        $em = $this->getDoctrine()->getManager();
            $localidad = $em->getRepository('ConfigBundle:Localidad')->findOneByByDefault(1);
            $entity->setLocalidad($localidad);
        $form   = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="proveedor_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(),'proveedor_edit');
        $em = $this->getDoctrine()->getManager();
        if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }
        $entity = $em->getRepository('AppBundle:Proveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el proveedor.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
    * @param Proveedor $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Proveedor $entity)
    {
        $form = $this->createForm(new ProveedorType(), $entity, array(
            'action' => $this->generateUrl('proveedor_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    /**
     * @Route("/{id}", name="proveedor_update")
     * @Method("PUT")
     * @Template("AppBundle:Proveedor:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(),'proveedor_edit');
        $em = $this->getDoctrine()->getManager();
        if ( $this->getUser()->getRol()->getAdmin() ) {
           $em->getFilters()->disable('softdeleteable');            
        }
        $entity = $em->getRepository('AppBundle:Proveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el proveedor.');
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            try {
                $em->flush();
                return $this->redirectToRoute('proveedor');
            } catch (\Exception $ex) {
                $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
            }
        }
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView()
        );
    }
    
    /**
     * @Route("/delete/{id}", name="proveedor_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), 'proveedor_delete');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $em->getConnection()->beginTransaction();
                $entity = $em->getRepository('AppBundle:Proveedor')->find($id);
                if (!$entity) {
                    throw $this->createNotFoundException('No existe el proveedor.');
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
                $this->addFlash('success', 'El proveedor fue eliminado!');
            } catch (\Exception $ex) {
                 $em->getConnection()->rollback();
                $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
            }
        }

        return $this->redirectToRoute('proveedor');
    }

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('proveedor_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }       
      
    /**
     * @Route("/ajax/renderAddProveedor", name="render_add_proveedor")
     * @Method("GET")
     */    
    public function renderAddProveedorAction()
    {
        UtilsController::haveAccess($this->getUser(),'proveedor_new');        
        $entity = new Proveedor();
        $form   = $this->createCreateForm($entity);

        $html = $this->renderView('AppBundle:Proveedor:partial-edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView() )
        );
        return new Response($html);       
    }    
    
    /**
     * @Route("/ajax/createProveedor", name="create_proveedor")
     * @Method("PUT")
     * @Template()
     */    
    public function createProveedor(Request $request){
        UtilsController::haveAccess($this->getUser(),'proveedor_new');
        $entity = new Proveedor();
        $form   = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $msg = 'No se pudo realizar esta operación.'.chr(13).' Verifique que no exista el valor que está intentando ingresar.';
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try{
                $em->persist($entity);
                $em->flush();
                $msg = 'OK';
            } catch (\Exception $ex) {
                $msg = UtilsController::errorMessage($ex->getErrorCode());
            }
        }
        $result = array('msg'=>$msg,'nombre'=>$entity->getNombre(),'id'=>$entity->getId());
        return new Response( json_encode($result) );  
    }     
    
    
}