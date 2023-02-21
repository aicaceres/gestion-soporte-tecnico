<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InsumoEntregaDetalleType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('barcode', null, array('mapped' => false))
                ->add('insumoId', 'hidden', array('mapped' => false))
                ->add('insumo', 'entity', array(
                    'class' => 'AppBundle:Insumo',
                    'required' => false,
                    'choice_label' => 'textoCompleto'))
                ->add('cantidad', null, array('required' => true))
                ->add('descripcion', null, array('required' => false, 'attr' => array('rows' => '1')));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\InsumoEntregaDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_insumoentregadetalle';
    }

}