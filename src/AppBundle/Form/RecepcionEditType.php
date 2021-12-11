<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use AppBundle\Form\CompraEditType;

class RecepcionEditType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('compra', new CompraEditType())
                ->add('fechaRecepcion', 'date', array('widget' => 'single_text', 'label' => 'Fecha Recepción:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('nroRemito', null, array('label' => 'N° Remito:', 'required' => true))
                ->add('observaciones', null, array('label' => 'Observaciones:', 'required' => false))
                ->add('file', 'file', array('label' => 'Comprobante:', 'required' => false))
                ->add('detalles', 'collection', array(
                    'type' => new RecepcionEditDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
                    'attr' => array(
                        'class' => 'row item'
            )))

        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\RecepcionCompra',
            'cascade_validation' => true,
            'error_bubbling' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_recepcioncompra';
    }

}