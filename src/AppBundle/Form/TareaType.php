<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TareaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $tarea = $options['data'];
        $otId = $tarea->getOrdenTrabajo()->getId();
        $ttId = $tarea->getTipoTarea()->getId();
        $tipoTarea = $tarea->getTipoTarea()->getAbreviatura();
        $opTt = array(
            'class' => 'ConfigBundle:TipoTarea',
            'query_builder' => function (\Doctrine\ORM\EntityRepository $em ) use ($ttId) {
                $qb = $em->createQueryBuilder('tt')
                        ->where("tt.id = " . $ttId);
                return $qb;
            }
        );
        $opOt = array(
            'class' => 'AppBundle:OrdenTrabajo',
            'required' => true,
            'query_builder' => function (\Doctrine\ORM\EntityRepository $em ) use ($otId) {
                $qb = $em->createQueryBuilder('ot')
                        ->where("ot.id = " . $otId);
                return $qb;
            }
        );
        $opDet = array(
            'class' => 'AppBundle:OrdenTrabajoDetalle',
            'label' => 'Equipos:', 'choice_label' => 'equipoTextoOT',
            'required' => false, 'multiple' => true, 'attr' => array('class' => 'select2'),
            'query_builder' => function (\Doctrine\ORM\EntityRepository $em ) use ($otId) {
                $qb = $em->createQueryBuilder('od')
                        ->innerJoin('od.ordenTrabajo', 'o')
                        ->where("o.id = " . $otId)
                        ->andWhere("od.entregado=0");
                return $qb;
            }
        );

        $builder
                ->add('fecha', 'date', array('widget' => 'single_text', 'label' => 'Fecha:',
                    'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('hora', null, array('mapped' => false, 'required' => true))
                ->add('tipoTarea', 'entity', $opTt)
                ->add('ordenTrabajo', 'entity', $opOt)
                ->add('ordenTrabajoDetalles', 'entity', $opDet);
        if (in_array($tipoTarea, array('NT', 'DS', 'TS', 'CE'))) {
            $builder->add('descripcion', null, array('label' => 'Descripción/Mensaje:'));
        }
        if (in_array($tipoTarea, array('RE', 'CE', 'TS'))) {
            $builder->add('textoAdicional', null, array('label' => 'Texto Adicional para Acta:'));
        }

        if (in_array($tipoTarea, array('RE', 'CE'))) {
            // ubicacion y estado
            $builder->add('estadoId', 'hidden', array('mapped' => false))
                    ->add('ubicacion', new EquipoUbicacionType(), array('mapped' => false));
        }
        if ($tipoTarea == 'CE') {
            // ubicacion y estado nuevos
            $builder->add('estadoNuevoId', 'hidden', array('mapped' => false))
                    ->add('ubicacionNueva', new EquipoUbicacionType(), array('mapped' => false));
        }
        if (in_array($tipoTarea, array('SI', 'PI'))) {
            // solicitud de hardware // pedido de insumos
            $builder->add('insumos', 'collection', array(
                'type' => new InsumoxTareaType(),
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => 'insitems',
                'attr' => array(
                    'class' => 'row item'
                ))
            );
        }

        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Tarea',
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_tarea';
    }

}