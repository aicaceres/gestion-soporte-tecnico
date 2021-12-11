<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductoBaseType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
        public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('barcode', null, array('attr'=>array('placeholder' => 'Código de barra','style' => 'text-transform:uppercase') 
                    ,'label' => 'Código de Barra:', 'required' => false))
                ->add('nroSerie', null, array('label' => 'N° de Serie:', 'required' => false, 'attr'=>array('placeholder' => 'N° Serie') ))
                ->add('nombre', null, array('label' => 'Descripción:','required' => false, 'attr'=>array('placeholder' => 'Descripción')))
                ->add('codigo', null, array('attr'=>array('placeholder' => 'Código','style' => 'text-transform:uppercase') 
                    ,'label' => 'Código:','required' => false))
                ->add('selmarca', 'entity', array('label' => 'Marca:', 'required' => false,
                    'attr' => array('class' => 'select2'),
                    'placeholder' => 'Marca...', 'class' => 'ConfigBundle:Marca'))
                ->add('selmodelo', 'entity', array('label' => 'Modelo:', 'required' => false,
                    'attr' => array('class' => 'select2'),
                    'placeholder' => 'Modelo...', 'class' => 'ConfigBundle:Modelo'))
                ->add('tipo', 'entity', array('attr' => array('class' => 'select2'),
                    'class' => 'ConfigBundle:Tipo','label' => 'Tipo Equipo/Insumo:',
                    'placeholder' => 'Tipo...',
                    'choice_label' => 'txtselect', 'required' => false, 'mapped' => false,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $em) {
                        return $em->createQueryBuilder('m')
                                ->orderBy("m.nombre");
                    }));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
           'cascade_validation' => true
        ));
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_producto';
    }
}
