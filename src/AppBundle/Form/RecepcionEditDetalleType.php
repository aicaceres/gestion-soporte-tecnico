<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecepcionEditDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('precio', null, array('required' => false))
                ->add('moneda', 'entity', array('required' => true, 'class' => 'ConfigBundle:Moneda'))
                ->add('cantidad', null, array('required' => true, 'label' => 'Cantidad:'))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\RecepcionCompraDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_recepcioncompradetalle';
    }

}