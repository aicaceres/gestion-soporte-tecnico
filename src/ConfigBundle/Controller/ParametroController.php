<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Tipo;
use ConfigBundle\Entity\Estado;
use ConfigBundle\Entity\ConceptoEntrega;
use ConfigBundle\Entity\Marca;
use ConfigBundle\Entity\Modelo;
use ConfigBundle\Entity\TipoSoporte;
use ConfigBundle\Entity\TipoVencimiento;

use ConfigBundle\Form\ParametroType;
/**
 * @Route("/parametro")
 */
class ParametroController extends Controller
{
    private $tableDesc = array('Tipo' => 'Tipos Equipo/Insumo','Estado' => 'Estados','ConceptoEntrega' => 'Conceptos de Entrega',
         'Marca'=>'Marcas y Modelos', 'Modelo'=>'Modelos', 'TipoSoporte'=>'Tipos Soporte Técnico', 'TipoVencimiento'=>'Tipos de Vencimiento');

    /**
     * @Route("/getAutocompleteTipo", name="get-autocomplete-tipo")
     * @Method("GET")
     */        
    public function getAutocompleteTipo(Request $request)
    {     
        $term = $request->get('q');
        $clase = $request->get('clase');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ConfigBundle:Tipo')->filterByTerm($clase,$term);    
        return new JsonResponse($results);       
    }     
    
    /*
     * Functions
     */
    private function getNewObject($obj) {
        switch ($obj) {
            case 'Tipo':
                $entity = new Tipo();
                break;
            case 'Estado':
                $entity = new Estado();
                break;
            case 'ConceptoEntrega':
                $entity = new ConceptoEntrega();
                break;
            case 'Marca':
                $entity = new Marca();
                break;
            case 'Modelo':
                $entity = new Modelo();
                break;
            case 'TipoSoporte':
                $entity = new TipoSoporte();
                break;
            case 'TipoVencimiento':
                $entity = new TipoVencimiento();
                break;
        }
        return $entity;
    }     
    
    /**
     * @Route("/{table}", name="configuracion_parametro")
     * @Method("GET")
     * @Template()
     */     
    public function indexAction($table)
    {
        UtilsController::haveAccess($this->getUser(),'configuracion_parametro');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:'.$table)->findAll();
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm( $table, $entity->getId())->createView();
        }
        $html = $this->renderView('ConfigBundle:Parametro:list.html.twig', 
                array('entities' => $entities,'table'=>$table, 'deleteForms' => $deleteForms )
        );
        return $this->render('ConfigBundle:Parametro:index.html.twig', array(
            'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));
    }    

     /**
     * @Route("/new/{table}", name="configuracion_parametro_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($table)
    {
        UtilsController::haveAccess($this->getUser(),'configuracion_parametro');
        $entity = $this->getNewObject($table);
        $urlCreate = $this->generateUrl('configuracion_parametro_create', array('table' => $table));    
        $form = $this->createForm(new ParametroType(), $entity, array(
                        'action' => $urlCreate, 'method' => 'PUT',
                    ));            

        $html = $this->renderView('ConfigBundle:Parametro:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table  )
        );
        return new Response($html);
        /*return $this->render('ConfigBundle:Parametro:index.html.twig', array(
            'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));  */  
    }
    
    /**
     * @Route("/create/{table}", name="configuracion_parametro_create")
     * @Method("PUT")
     * @Template()
     */    
    public function createAction(Request $request, $table){
        UtilsController::haveAccess($this->getUser(),'configuracion_parametro');
        $entity = $this->getNewObject($table);
        $urlCreate = $this->generateUrl('configuracion_parametro_create', array('table' => $table));    
        $form = $this->createForm(new ParametroType(), $entity, array(
                        'action' => $urlCreate, 'method' => 'PUT',
                    )); 
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try{
                $em->persist($entity);
                $em->flush();
                $this->addFlash('success','Creado con éxito!' );
                 return $this->redirectToRoute('configuracion_parametro',array('table'=>$table));
            } catch (\Exception $ex) {
                $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
            }
        }
         $deleteForm = $this->createDeleteForm($table,0);
        $html = $this->renderView('ConfigBundle:Parametro:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 
                    'table' => $table ,'delete_form' => $deleteForm->createView() )
        );
        return $this->render('ConfigBundle:Parametro:index.html.twig', array(
            'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));
    }    
        
    /**
     * @Route("/{table}/{id}/edit", name="configuracion_parametro_edit")
     * @Method("GET")
     * @Template()
     */    
    public function editAction($table,$id){
        UtilsController::haveAccess($this->getUser(),'configuracion_parametro');
        $em = $this->getDoctrine()->getManager();   
        $entity = $em->getRepository('ConfigBundle:'.$table)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe');
        }
        $urlUpdate = $this->generateUrl('configuracion_parametro_update', 
                array('table' => $table,'id' => $entity->getId()) );

        $form = $this->createForm(new ParametroType(), $entity, array(
            'action' => $urlUpdate,
            'method' => 'PUT',
        ));
        $deleteForm = $this->createDeleteForm($table,$id);
        $html = $this->renderView('ConfigBundle:Parametro:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 
                      'table' => $table, 'delete_form' => $deleteForm->createView()   )
        );
        return new Response($html);
       /* return $this->render('ConfigBundle:Parametro:index.html.twig', array(
            'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));  */  
    }    
    
    /**
     * @Route("/update/{table}/{id}", name="configuracion_parametro_update")
     * @Method("PUT")
     * @Template()
     */    
    public function updateAction(Request $request, $table, $id) {
        UtilsController::haveAccess($this->getUser(),'configuracion_parametro');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:' . $table)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe.');
        }
        if($table=='Marca'){
            $original = new ArrayCollection();
            foreach ($entity->getModelos() as $item) {
                $original->add($item);
            }
        }
        $urlUpdate = $this->generateUrl('configuracion_parametro_update', array('table' => $table, 'id' => $entity->getId()));

        $form = $this->createForm(new ParametroType(), $entity, array(
            'action' => $urlUpdate,
            'method' => 'PUT',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            try{
                if($table=='Marca'){
                    // remove the relationship 
                    foreach ($original as $item) {
                        if (false === $entity->getModelos()->contains($item)) {
                             $em->remove($item);
                        }
                    }
                }
                $em->persist($entity);
                $em->flush();
                $this->addFlash('success','Modificado con éxito!' ); 
                return $this->redirectToRoute('configuracion_parametro',array('table'=>$table));
            } catch (\Exception $ex) {
                $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
                $entity = $em->getRepository('ConfigBundle:' . $table)->find($id);
                $form = $this->createForm(new ParametroType(), $entity, array(
                    'action' => $urlUpdate,
                    'method' => 'PUT',
                ));
            }    
        }
        $deleteForm = $this->createDeleteForm($table,$id);
        $html = $this->renderView('ConfigBundle:Parametro:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 
                    'table' => $table, 'delete_form' => $deleteForm->createView()  )
        );
        return $this->render('ConfigBundle:Parametro:index.html.twig', array(
            'title' => $this->tableDesc[$table], 'html' => json_encode($html)
        ));
    }    
    
    /**
     * @Route("/delete/{table}/{id}", name="configuracion_parametro_delete")
     * @Method("DELETE")
     */
    public function deleteAction($table, $id)
    {
        UtilsController::haveAccess($this->getUser(),'configuracion_parametro');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:'.$table)->find($id);
        try{
            $em->remove($entity);
            $em->flush();
            $this->addFlash('success','Se ha eliminado con éxito!' ); 
        } catch (\Exception $ex) {
            $this->addFlash('danger',UtilsController::errorMessage($ex->getErrorCode()) ); 
        }        
        return $this->redirectToRoute('configuracion_parametro',array('table'=>$table));
    }    
    
    private function createDeleteForm($table,$id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('configuracion_parametro_delete', array('table'=>$table , 'id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }           

    /**
     * @Route("/selectModelos", name="select_modelos")
     * @Method("POST")
     */    
    public function selectModelosAction(Request $request)
    {
        $marcaId = $request->request->get('marca_id');
        $em = $this->getDoctrine()->getManager();
        $modelos = $em->getRepository('ConfigBundle:Modelo')->findByMarcaId($marcaId);
        return new JsonResponse($modelos);
    } 
    
    /**
     * @Route("/ajax/renderAddParametro", name="render_add_parametro")
     * @Method("GET")
     */    
    public function renderAddParametroAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(),'configuracion_parametro');
        $table = $request->get('tabla');
        $entity = $this->getNewObject($table);
        switch ($table) {
            case 'Tipo':                
                $entity->setClase( $request->get('clase') ); 
                break;
            case 'Modelo':
                $em = $this->getDoctrine()->getManager();
                $marca = $em->getRepository('ConfigBundle:Marca')->find( $request->get('marca') );
                $entity->setMarca($marca);
                break;
        }
        $urlCreate = $this->generateUrl('configuracion_parametro_create', array('table' => $table));    
        $form = $this->createForm(new ParametroType(), $entity, array(
                        'action' => $urlCreate, 'method' => 'PUT',
                    ));            

        $html = $this->renderView('ConfigBundle:Parametro:partial-edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table  )
        );
        return new Response($html);       
    }    
    
    /**
     * @Route("/ajax/createParametro", name="create_parametro")
     * @Method("PUT")
     * @Template()
     */    
    public function createParametro(Request $request){
        $table = $request->get('table');
        $entity = $this->getNewObject($table);
        $urlCreate = $this->generateUrl('configuracion_parametro_create', array('table' => $table));    
        $form = $this->createForm(new ParametroType(), $entity, array(
                        'action' => $urlCreate, 'method' => 'PUT',
                    )); 
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