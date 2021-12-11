<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
class RecepcionCompraType extends AbstractType
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
            'label'         => 'Depósito:',
            'required'      => false,  
            'choice_label'  => 'nombreCompleto',
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('d')
                    ->where('d.deposito = 1')
                    ->orderBy('d.nombre');
                return $qb;
            }
        );
        $builder
            ->add('fechaRecepcion','date',array('widget' => 'single_text', 'label'=>'Fecha Recepción:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('nroFactura',null,array('label'=>'N° Factura:', 'required' => false))    
            ->add('nroRemito',null,array('label'=>'N° Remito:', 'required' => true))                   
            ->add('observaciones',null,array('label'=>'Observaciones:', 'required' => false)) 
            ->add('deposito', 'entity', $formOptions) 
            ->add('file', 'file', array('label'=>'Comprobante:', 'required' => false))    
            ->add('detalles', 'collection', array(
                'type'           => new RecepcionCompraDetalleType(),
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
            'data_class' => 'AppBundle\Entity\RecepcionCompra'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_recepcioncompra';
    }
}
