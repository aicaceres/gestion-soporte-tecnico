<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipoBaseType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('barcode',null, array('label'=>'Código de Barra:'))
            ->add('nroSerie',null, array('label'=>'N° de Serie:'))
            ->add('nombre',null, array('label'=>'Descripción:'))
            ->add('codigo',null, array('label'=>'Código:'))
            ->add('marca','entity',array('label'=>'Marca:','required' => false,
                  'placeholder'=>'Seleccionar...', 'class' => 'ConfigBundle:Marca'))
            ->add('modelo','entity',array('label'=>'Modelo:','required' => false,
                  'placeholder'=>'Seleccionar...','class' => 'ConfigBundle:Modelo'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Equipo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_equipo';
    }
}
