<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpresaType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre',null,array('label'=>'Nombre de la Empresa:'))
            ->add('leyenda',null,array('label'=>'Leyenda:'))
            ->add('leyendaFactura',null,array('label'=>'Leyenda para comprobantes:', 'required' => false))
            ->add('cuit',null,array('label'=>'CUIT:'))
            ->add('direccion',null,array('label'=>'Dirección:'))
            ->add('telefono',null,array('label'=>'Teléfono:'))
            ->add('email',null,array('label'=>'Correo electrónico:'))
                ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\Empresa'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_empresa';
    }
}
