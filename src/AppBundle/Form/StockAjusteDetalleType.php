<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockAjusteDetalleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('signo','choice', array('choices' => array( '+'=>'+','-'=>'-')))
                ->add('insumo', 'entity', array(
                    'required' => true,
                    'placeholder' => 'Seleccionar Insumo...',
                    'class' => 'AppBundle\\Entity\\Insumo',
                    'attr'  => array('class' => 'select2','label'=>'Insumo:')
                    ))
                ->add('cantidad',null,array('required' => true,'label'=>'Cantidad:'))
                ->add('motivo',null,array('label' => 'Motivo:','required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\StockAjusteDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_stockajustedetalle';
    }
}