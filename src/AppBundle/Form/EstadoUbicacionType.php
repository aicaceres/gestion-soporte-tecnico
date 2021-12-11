<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstadoUbicacionType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder->add('otDetalle','hidden')
                ->add('src','hidden')
                ->add('estado','entity',array('class'=> 'ConfigBundle:Estado',
                      'required'=> true,'label'=> 'Estado:','attr' => array('class'=>'select2')))
                ->add('ubicacion',new EquipoUbicacionType(),array('required'=> true));                       
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_estadoubicacion';
    }
}
