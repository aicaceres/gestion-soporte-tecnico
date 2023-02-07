<?php

namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametroType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $options['data'];

        $builder->add('nombre', null, array('label' => 'Descripción:'));

        if (property_exists($data, 'abreviatura')) {
            $builder->add('abreviatura', null,
                    array('label' => 'Abreviatura:', 'required' => true));
        }
        if (property_exists($data, 'clase')) {
            $builder->add('clase', 'choice', array('choices' => array(
                    'Equipo' => 'E', 'Insumo' => 'I'),
                'label' => 'Clase:',
                'choices_as_values' => true, 'multiple' => false, 'expanded' => true));
        }
        if (property_exists($data, 'subclase')) {
            $builder->add('subclase', 'choice', array('choices' => array(
                    'Hardware' => 'HARDWARE', 'Insumo' => 'INSUMO'),
                'label' => 'Subclase:',
                'empty_data' => 'HARDWARE',
                'choices_as_values' => true, 'multiple' => false, 'expanded' => true));
        }
        if (property_exists($data, 'modelos')) {
            $builder->add('modelos', 'collection', array(
                'type' => new ModeloType(),
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'cascade_validation' => true,
                'prototype_name' => 'itemform',
                'attr' => array(
                    'class' => 'row item'
            )));
        }
        if (property_exists($data, 'marca')) {
            $builder->add('marca', 'entity', array('label' => 'Marca:',
                'class' => 'ConfigBundle:Marca', 'required' => true));
        }
        if (property_exists($data, 'inicial')) {
            $builder->add('inicial', null, array('label' => 'Inicial', 'required' => false));
        }
        if (property_exists($data, 'activo')) {
            $builder->add('activo', null, array('label' => 'Activo', 'required' => false));
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'configbundle_parametro';
    }

}