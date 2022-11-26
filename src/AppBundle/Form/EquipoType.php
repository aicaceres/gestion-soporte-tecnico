<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipoType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('nroSerie', null, array('label' => 'N° de Serie:'))
                ->add('nombre', null, array('label' => 'Descripción:'))
                //    ->add('codigo',null, array('label'=>'Código:'))
                ->add('savenew', 'hidden', array('mapped' => false, 'required' => false))
                // ->add('file', 'file', array('label'=>'Fotografía:', 'required' => false))
                ->add('observaciones', null, array('label' => 'Observaciones:', 'required' => false))
                ->add('estado', 'entity', array('label' => 'Estado:', 'required' => true,
                    'class' => 'ConfigBundle:Estado'))
                ->add('marca', 'entity', array('label' => 'Marca:', 'required' => true,
                    'placeholder' => 'Seleccionar...', 'class' => 'ConfigBundle:Marca'))
                ->add('modelo', 'entity', array('label' => 'Modelo:', 'required' => true,
                    'placeholder' => 'Seleccionar...', 'class' => 'ConfigBundle:Modelo'))
                ->add('proveedor', 'entity', array('label' => 'Proveedor:',
                    'class' => 'AppBundle:Proveedor',
                    'placeholder' => 'Seleccionar...',
                    'required' => false))
                ->add('verificado', null, array('label' => ' VERIFICADO', 'required' => false))
                /* ->add('fechaCompra',NULL,array('widget' => 'single_text','label' => 'Fecha Compra:',
                  'format' => 'dd-MM-yyyy', 'required' => true, 'html5' => false,
                  'attr' => array('class' => 'datepicker'))) */
                ->add('fechaCompra', 'date', array('widget' => 'single_text', 'label' => 'Fecha Compra:',
                    'format' => 'dd-MM-yyyy', 'required' => false))
                ->add('inicioVidaUtil', 'date', array('widget' => 'single_text', 'label' => 'Vida Útil:',
                    'format' => 'dd-MM-yyyy', 'required' => false))
                ->add('nroOrdenCompra', null, array('label' => 'N° O.C.:'))
                ->add('nroFactura', null, array('label' => 'N° Factura:'))
                ->add('nroRemito', null, array('label' => 'N° Remito:'))
                ->add('cotizacionDolar', null, array('label' => 'Cotización U$S:', 'required' => false))
                ->add('precio', null, array('label' => 'Precio:', 'required' => false))
                ->add('moneda', 'entity', array('required' => true, 'class' => 'ConfigBundle:Moneda'))
                ->add('tipo', 'entity', array(
                    'class' => 'ConfigBundle:Tipo',
                    'label' => 'Tipo de Equipo:',
                    'choice_label' => 'nombre', 'required' => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $em) {
                        return $em->createQueryBuilder('m')
                                ->where("m.clase = 'E' ");
                    }))
                ->add('ubicaciones', 'collection', array(
                    'type' => new EquipoUbicacionType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'itemform',
                    'attr' => array(
                        'class' => 'row item'
        )));
        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Equipo'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_equipo';
    }

}