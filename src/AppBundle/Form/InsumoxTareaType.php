<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InsumoxTareaType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {                
        $builder            
            ->add('insumo', 'entity', array(
                        'class' => 'AppBundle:Insumo',
                        'required' => false                
                    ))               
            ->add('descripcion',null,array('required' => true))
            ->add('cantidad',null,array('required' => true))                    
            ->add('codigo',null,array('mapped' => false));                        
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\InsumoxTarea'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_insumoxtarea';
    }
}
