<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VencimientoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('detalle',null,array('label'=>'Detalle:', 'required' => true))                     
                ->add('ordenCompra',null,array('label'=>'N° OC:', 'required' => false))   
                ->add('observaciones',null,array('label'=>'Observaciones:', 'required' => false))     
                ->add('savenew','hidden',array('mapped' => false,'required' => false))
                ->add('tipo','entity',array('label'=>'Tipo:','required' => true,
                      'class' => 'ConfigBundle:TipoVencimiento'))
                ->add('proveedor','entity',array('label'=>'Proveedor:',
                      'class' => 'AppBundle:Proveedor', 'required' =>true,
                      'attr'  => array('class' => 'select2')))
                ->add('fechaInicio','date',array('widget' => 'single_text', 'label'=>'Fecha Inicio:',
                      'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('fechaFin','date',array('widget' => 'single_text', 'label'=>'Fecha Fin:',
                      'format' => 'dd-MM-yyyy', 'required' => true))
                ->add('periodo',null,array('label'=>'Periodo:', 'required' => false))  
                ->add('moneda','entity',array('required'=>true, 'class' => 'ConfigBundle:Moneda')) 
                ->add('abono',null,array('label'=>'Abono:', 'required' => false));              
                                
        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));                        
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Vencimiento'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_vencimiento';
    }
}
