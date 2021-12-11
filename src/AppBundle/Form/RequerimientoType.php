<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
class RequerimientoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $req = $options['data'];    
        if($req->getDepartamentoEquipo()){
            $depId = $req->getDepartamentoEquipo()->getId();            
            $pisoOptions = array(
                'class'         => 'ConfigBundle:Piso',            
                'required'      => true, 
                'query_builder' => function (\Doctrine\ORM\EntityRepository $em ) use($depId) {
                    $qb = $em->createQueryBuilder('p')
                            ->innerJoin('p.departamentos','d')
                                    ->where("d.id=".$depId);
                    return $qb;
                }
            ); 
        }else{
            $pisoOptions = array(
                'class'         => 'ConfigBundle:Piso',            
                'required'      => true, 
                'query_builder' => function (\Doctrine\ORM\EntityRepository $em ) {
                    $qb = $em->createQueryBuilder('p');
                    return $qb;
                }
            ); 
        } 
        if($req->getEstado()== 'SIN ASIGNAR'){
            $tecOptions = array(
                'class'         => 'ConfigBundle:Usuario',            
                'label'         => 'Asignar al técnico:',
                'choice_label'  => 'nombre',
                'required'      => false,  
                'mapped'      => false,  
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('u')
                                    ->innerJoin('u.rol','r')
                                    ->where("r.tecnico = 1 ")
                                    ->orderBy('u.nombre');
                    return $qb;
                }
            );
            $builder->add('tecnico', 'entity', $tecOptions);
        }         
        
        $builder
            ->add('altaPrioridad',null,array('label' => ' Prioridad Alta','required'=>false))        
            ->add('fechaRequerimiento','date',array('widget' => 'single_text', 'label'=>'Fecha:',
                'format' => 'dd-MM-yyyy', 'required' => true)) 
            ->add('hora',null,array('mapped'=>false, 'required'=>true))         
            ->add('jira', null, array('label' => 'N° JIRA:','required' => false))
            ->add('tipoSoporte','entity',array('label'=>'Tipo de Incidencia:','required' => false,
                  'placeholder'=>'Seleccionar...', 'class' => 'ConfigBundle:TipoSoporte'))            
            ->add('responsable',null, array('label'=>'Nombre del Solicitante:'))
            ->add('descripcion', null, array('label' => 'Descripción del Requerimiento:', 
                'required' => false))   
            ->add('textoActaRecepcion', null, array('label' => 'Texto adicional para el acta de recepción:', 
                'required' => false,'attr'=>array('rows'=>'1')))    
            ->add('ordentrabajoAsociadas', 'collection', array(                
                'type'           => new OrdenTrabajoType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'otitems',
                'attr'           => array(
                    'class' => 'row otitem'
                )))           
            ->add('otid','hidden',array('mapped'=>false))    
                ;             
        
        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));                        
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Requerimiento'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_requerimiento';
    }
}