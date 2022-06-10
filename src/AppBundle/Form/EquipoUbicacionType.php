<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipoUbicacionType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('fechaEntrega', NULL, array('widget' => 'single_text', 'label' => 'Fecha de Entrega:',
                    'format' => 'dd-MM-yyyy', 'html5' => false, 'required' => FALSE,
                    'attr' => array('class' => 'datepicker')))
                ->add('ubicacion', 'entity', array('label' => 'Ubicación:', 'required' => false,
                    'class' => 'ConfigBundle:Ubicacion'))
                ->add('edificio', 'entity', array('label' => 'Edificio:', 'required' => true,
                    'class' => 'ConfigBundle:Edificio'))
                ->add('departamento', 'entity', array('label' => 'Departamento:', 'required' => true,
                    'class' => 'ConfigBundle:Departamento'))
                ->add('piso', 'entity', array('label' => 'Piso:', 'required' => true,
                    'class' => 'ConfigBundle:Piso', 'attr' => array('class' => 'piso_selector')))
                ->add('conceptoEntrega', 'entity', array('label' => 'Concepto de Entrega:', 'required' => true,
                    'class' => 'ConfigBundle:ConceptoEntrega'))
                ->add('observaciones', null, array('label' => 'Observaciones:', 'required' => false))
                ->add('redIp', null, array('label' => 'Red:', 'required' => false, 'attr' => array('class' => 'ip_address')))
                ->add('actual', 'hidden', array('required' => false))
        ;

        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\EquipoUbicacion'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_equipoubicacion';
    }

}