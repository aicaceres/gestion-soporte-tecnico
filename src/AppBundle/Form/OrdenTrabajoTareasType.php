<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class OrdenTrabajoTareasType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
        $tecOptions = array(
            'class'         => 'ConfigBundle:Usuario',            
            'label'         => 'Técnico:',
            'choice_label'  => 'nombre',
            'required'      => true, 
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('u')
                                ->innerJoin('u.rol','r')
                                ->where("r.tecnico = 1 ")
                                ->orderBy('u.nombre');
                return $qb;
            }
        );
        $builder
            ->add('estado','choice', array('choices' => array( 'ABIERTO'=>'Abierto',
                'CERRADO'=>'Cerrado','CANCELADO'=>'Cancelado'), 'label'=>'Estado:'))    
            ->add('tecnico', 'entity', $tecOptions)    
            ->add('tareas', 'collection', array(                
                'type'           => new TareaType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'items',
                'attr'           => array(
                    'class' => 'row item'
                )))    ;                          
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\OrdenTrabajo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_ordentrabajo_tareas';
    }
}
