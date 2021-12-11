<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
class RequerimientoDetalleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder
                ->add('barcode',null,array('mapped'=>false))  
                ->add('equipoId','hidden',array('mapped'=>false))
                ->add('equipo','entity',array(
                    'class'         => 'AppBundle:Equipo',      
                    'required' => false,
                    'choice_label'  => 'textoOT')) 
                ->add('descripcion', null, array('required' => false,'attr'=>array('rows'=>'1')));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\RequerimientoDetalle'
        ));
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_requerimientodetalle';
    }
}