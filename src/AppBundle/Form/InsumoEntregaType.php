<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class InsumoEntregaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $depositoOptions = array(
            'class' => 'ConfigBundle:Departamento',
            'label' => 'Depósito:',
            'required' => true,
            'choice_label' => 'nombre',
            'attr' => array('class' => 'select2'),
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('d')
                        ->innerJoin('d.edificio', 'e')
                        ->innerJoin('e.usuarios', 'us')
                        ->where('d.depositoEntrega=1')
                        ->orderBy('d.nombre');
                return $qb;
            }
        );
        $builder
                ->add('fecha', 'date', array('widget' => 'single_text', 'label' => 'Fecha:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('hora', null, array('mapped' => false, 'required' => true))
                ->add('jira', null, array('label' => 'N° JIRA:', 'required' => false))
                ->add('solicitante', 'entity', array('label' => 'Area Solicitante:',
                    'class' => 'ConfigBundle:Departamento', 'required' => true,
                    'attr' => array('class' => 'select2')))
                ->add('responsable', null, array('label' => 'Nombre del Solicitante:'))
                ->add('estado', 'choice', array('choices' => array('ENTREGADO' => 'Entregado', 'PENDIENTE' => 'Pendiente',
                        'CANCELADO' => 'Cancelado'), 'label' => 'Estado:',
                    'attr' => array('class' => 'select2'), 'required' => true))
                ->add('observacion', null, array('label' => 'Observaciones:',
                    'required' => false))
                ->add('textoRemito', null, array('label' => 'Texto adicional para el remito:',
                    'required' => false, 'attr' => array('rows' => '1')))
                ->add('deposito', 'entity', $depositoOptions)
                ->add('detalles', 'collection', array(
                    'type' => new InsumoEntregaDetalleType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'items',
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
            'data_class' => 'AppBundle\Entity\InsumoEntrega'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_insumoentrega';
    }

}