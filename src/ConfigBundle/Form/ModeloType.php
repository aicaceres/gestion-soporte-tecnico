<?php
namespace ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModeloType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
     public function buildForm(FormBuilderInterface $builder, array $options) {
         $builder->add('nombre', 'text', array('label' => 'Nombre:','required'=>true))
                 ->add('file', 'file', array('label'=>'Fotografía:', 'required' => false));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Modelo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_modelo';
    }

}
