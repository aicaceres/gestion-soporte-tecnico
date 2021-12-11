<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InsumoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('barcode',null, array('label'=>'Código de Barra:'))
            //->add('nombre',null, array('label'=>'Descripción:'))
            //->add('codigo',null, array('label'=>'Código:'))
            ->add('stockMinimo',null, array('label'=>'Stock Mínimo:'))
            ->add('savenew','hidden',array('mapped' => false,'required' => false))    
            ->add('marca','entity',array('label'=>'Marca:','required' => false,
                  'placeholder'=>'Seleccione Marca', 'class' => 'ConfigBundle:Marca'))
            ->add('modelo','entity',array('label'=>'Modelo:','required' => false,
                  'placeholder'=>'Seleccione Modelo','class' => 'ConfigBundle:Modelo'))
            ->add('tipo','entity',array(
                    'class' => 'ConfigBundle:Tipo',
                    'label' => 'Tipo de Insumo:',
                    'choice_label' => 'nombre','required' => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $em) {
                        return $em->createQueryBuilder('m')
                                ->where("m.clase = 'I' "); } ));         
                                
        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));                        
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Insumo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_insumo';
    }
}
