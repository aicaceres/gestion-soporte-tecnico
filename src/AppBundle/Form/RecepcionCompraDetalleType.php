<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecepcionCompraDetalleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('compraDetalle','entity',array('class' => 'AppBundle:CompraDetalle','choice_label' => 'id'))  
                ->add('insumo')
                ->add('nroSerie',null, array('mapped'=>false,'required'=>false))
                ->add('precio',null,array('required'=>false))  
                ->add('moneda','entity',array('required'=>true, 'class' => 'ConfigBundle:Moneda')) 
                ->add('cantidad',null,array('required' => true,'label'=>'Cantidad:'))
                ->add('nombre', null, array('mapped' =>false,'required' => false, 'attr'=>array('placeholder' => 'Código de Barra')))                
                //->add('nombre', null, array('mapped' =>false,'required' => false, 'attr'=>array('placeholder' => 'Descripción')))                
                ->add('itemMarca', 'entity', array('required' => false,'mapped' =>false,
                    'placeholder' => 'Marca...', 'class' => 'ConfigBundle:Marca'))
                ->add('itemModelo', 'entity', array('mapped' =>false, 'required' => false,
                    'placeholder' => 'Modelo...', 'class' => 'ConfigBundle:Modelo'))                
                ->add('tipo', 'entity', array('mapped' =>false,'class' => 'ConfigBundle:Tipo',
                    'placeholder' => 'Tipo...','required' => false))                
                ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\RecepcionCompraDetalle'
        ));
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_recepcioncompradetalle';
    }
}