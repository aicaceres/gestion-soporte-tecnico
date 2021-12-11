<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompraEditType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('descripcion', null, array('label' => 'Observaciones:', 'required' => false))
                ->add('nroFactura', null, array('label' => 'N° Factura:', 'required' => false))
                ->add('fechaCompra', 'date', array('widget' => 'single_text', 'label' => 'Fecha Compra:',
                    'format' => 'dd-MM-yyyy', 'required' => true));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compra'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_compraedit';
    }

}