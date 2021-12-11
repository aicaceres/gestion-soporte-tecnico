<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompraDetalleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('insumo')
              //  ->add('producto',new ProductoBaseType(),array('mapped'=>false))                
                //->add('codigo',null,array('attr'=>array('style' => 'text-transform:uppercase') ,'required'=>false))                
                ->add('cantidad',null,array('required' => true,'label'=>'Cantidad:'))
                ->add('precio',null,array('label' => 'Costo:','required'=>false))    
                ->add('moneda','entity',array('required'=>true, 'class' => 'ConfigBundle:Moneda'))                    
                ->add('nombre', null, array('required' => false, 'attr'=>array('placeholder' => 'Código de Barra')))                
                ->add('itemMarca', 'entity', array('label' => 'Marca:', 'required' => false,
                    'attr' => array('class' => 'select2'),
                    'placeholder' => 'Marca...', 'class' => 'ConfigBundle:Marca'))
                ->add('itemModelo', 'entity', array('label' => 'Modelo:', 'required' => false,
                    'attr' => array('class' => 'select2'),
                    'placeholder' => 'Modelo...', 'class' => 'ConfigBundle:Modelo'))                
                ->add('tipo', 'entity', array('class' => 'ConfigBundle:Tipo',
                    'placeholder' => 'Tipo...','required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CompraDetalle'
        ));
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compradetalle';
    }
}