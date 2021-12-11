<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ConfigBundle\Form\EventListener\AddLocalidadFieldSubscriber;
use ConfigBundle\Form\EventListener\AddProvinciaFieldSubscriber;
use ConfigBundle\Form\EventListener\AddPaisFieldSubscriber;

class ProveedorType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyPathToLoalidad = 'localidad';
        $builder->addEventSubscriber(new AddPaisFieldSubscriber($propertyPathToLoalidad))
                    ->addEventSubscriber(new AddProvinciaFieldSubscriber($propertyPathToLoalidad))
                    ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLoalidad));
        $builder
            ->add('nombre',null, array('label'=>'Nombre:'))
            ->add('direccion', 'text', array('label' => 'Dirección:','required'=>false))
            ->add('telefono', 'text', array('label' => 'Teléfono:','required'=>false))
            ->add('email', 'email', array('label' => 'Email:','required'=>false))
            ->add('savenew','hidden',array('mapped' => false,'required' => false));   
                                
        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));                        
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Proveedor'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_proveedor';
    }
}