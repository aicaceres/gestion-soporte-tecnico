<?php
namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartamentoProveedorType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('enlaceProveedor',null,array('label' => 'Proveedor:', 'required' => false)) 
                ->add('enlaceTipoConexion', null, array('label' => 'Tipo conexión - Servicio Contratado:', 'required' => false))
                ->add('enlaceTelefonoReclamo',null, array('label' => 'Tel. Contacto Reclamos:', 'required' => false))
                ->add('enlaceEmailReclamo','email', array('label' => 'Mail Contacto Reclamos:', 'required' => false))
                ->add('enlaceReferenciaCliente',null, array('label' => 'Referencia - Nombre Cliente:', 'required' => false))
                ->add('internetProveedor',null,array('label' => 'Proveedor:', 'required' => false)) 
                ->add('internetTipoConexion', null, array('label' => 'Tipo conexión - Servicio Contratado:', 'required' => false))
                ->add('internetTelefonoReclamo',null, array('label' => 'Tel. Contacto Reclamos:', 'required' => false))
                ->add('internetEmailReclamo','email', array('label' => 'Mail Contacto Reclamos:', 'required' => false))
                ->add('internetReferenciaCliente',null, array('label' => 'Referencia - Nombre Cliente:', 'required' => false))
                ->add('observaciones', 'textarea', array('label' => 'Observaciones:', 'required' => false));                           
    }
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ConfigBundle\Entity\DepartamentoProveedor'
        ));
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'configbundle_departamentoproveedor';
    }
}