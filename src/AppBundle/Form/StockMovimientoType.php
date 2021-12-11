<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class StockMovimientoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $formOptions = array(
            'class'         => 'ConfigBundle:Departamento',
            'placeholder'   => 'Seleccione Depósito',
            'required'      => true,              
            'attr'          => array('class'=>'select2'),
            'choice_label'  => 'nombreCompleto',
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('d')
                    ->where('d.deposito = 1')
                    ->orderBy('d.nombre');
                return $qb;
            }
        );
        $builder
            ->add('fecha','date',array('widget' => 'single_text', 'label'=>'Fecha:',
                'format' => 'dd-MM-yyyy', 'required' => true))   
            ->add('depositoOrigen', 'entity', $formOptions) 
            ->add('depositoDestino', 'entity', $formOptions) 
            ->add('observaciones',null,array('label' => 'Observaciones:','required' => false))             
            ->add('detalles', 'collection', array(
                'type'           => new StockMovimientoDetalleType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'items',
                'attr'           => array(
                    'class' => 'row item'
                )))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\StockMovimiento'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_stockmovimiento';
    }
}