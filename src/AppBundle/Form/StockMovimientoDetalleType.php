<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockMovimientoDetalleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('insumo', 'entity', array(
                    'required' => true,
                    'placeholder' => 'Seleccionar Insumo...',
                    'class' => 'AppBundle\\Entity\\Insumo',
                    'attr'  => array('class' => 'select2','label'=>'Insumo:')
                    ))
                ->add('cantidad',null,array('required' => true,'label'=>'Cantidad:'))                
               // ->add('codigo',null,array('required' => false,'label'=>'Código:','mapped'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\StockMovimientoDetalle'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_stockmovimientodetalle';
    }
}