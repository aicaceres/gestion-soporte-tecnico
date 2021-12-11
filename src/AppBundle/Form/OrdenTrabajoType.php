<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class OrdenTrabajoType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $tecOptions = array(
            'class' => 'ConfigBundle:Usuario',
            'label' => 'Técnico:',
            'choice_label' => 'nombre',
            'required' => true,
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('u')
                        ->innerJoin('u.rol', 'r')
                        ->where("r.tecnico = 1 ")
                        ->orderBy('u.nombre');
                return $qb;
            }
        );
        $builder
                ->add('altaPrioridad', null, array('label' => ' Prioridad Alta', 'required' => false))
                ->add('fechaOrden', 'date', array('widget' => 'single_text', 'label' => 'Fecha:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('hora', null, array('mapped' => false, 'required' => true))
                ->add('descripcion', null, array('label' => 'Descripción de la orden de trabajo:',
                    'required' => false))
                ->add('jira', null, array('label' => 'N° JIRA:', 'required' => false))
                ->add('tecnico', 'entity', $tecOptions)
                ->add('documentos', 'collection', array(
                    'type' => new DocumentacionType(),
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => 'itemdoc',
                    'attr' => array(
                        'class' => 'row item'
            )))
        ;
        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\OrdenTrabajo'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_ordentrabajo';
    }

}