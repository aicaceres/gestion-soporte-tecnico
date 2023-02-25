<?php

namespace ConfigBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use ConfigBundle\Form\EventListener\AddLocalidadFieldSubscriber;
use ConfigBundle\Form\EventListener\AddProvinciaFieldSubscriber;
use ConfigBundle\Form\EventListener\AddPaisFieldSubscriber;

class UbicacionType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $options['data'];

        $builder->add('nombre', null, array('label' => 'Nombre:'));
        if (property_exists($data, 'abreviatura')) {
            $builder->add('abreviatura', null,
                    array('label' => 'Abreviatura:', 'required' => true));
        }
        if (property_exists($data, 'deposito')) {
            $builder->add('deposito', null,
                    array('label' => 'Es depósito', 'required' => false));
        }
        if (property_exists($data, 'depositoEntrega')) {
            $builder->add('depositoEntrega', null,
                    array('label' => 'Entrega de Insumos', 'required' => false));
        }
        if (property_exists($data, 'razonSocial')) {
            $builder->add('razonSocial', null,
                    array('label' => 'Ver en compras', 'required' => false));
        }
        if (property_exists($data, 'servicioTecnico')) {
            $builder->add('servicioTecnico', null,
                    array('label' => 'Servicio Técnico', 'required' => false));
        }
        if (property_exists($data, 'ubicacion')) {
            $builder->add('ubicacion', 'entity', array('label' => 'Ubicación:',
                'class' => 'ConfigBundle:Ubicacion', 'required' => true));
        }
        if (property_exists($data, 'edificio')) {
            $builder->add('ubicacion', 'entity', array('label' => 'Ubicación:', 'mapped' => false,
                        'class' => 'ConfigBundle:Ubicacion', 'choice_label' => 'nombre', 'required' => true))
                    ->add('edificio', 'entity', array('label' => 'Edificio:',
                        'class' => 'ConfigBundle:Edificio', 'required' => true));
        }
        if (property_exists($data, 'direccion')) {
            $propertyPathToLoalidad = 'localidad';
            $builder->add('direccion', null, array('label' => 'Dirección:', 'required' => true))
                    ->add('telefono', null, array('label' => 'Teléfonos:', 'required' => false))
                    ->add('email', 'email', array('label' => 'Email:', 'required' => false))
                    ->add('responsable', null, array('label' => 'Responsable:', 'required' => false))
                    ->add('observaciones', null, array('label' => 'Observaciones:', 'required' => false))
                    ->add('pisos', 'entity', array(
                        'class' => 'ConfigBundle:Piso',
                        'property' => 'nombre',
                        'label' => 'Pisos:',
                        'multiple' => true,
                        'required' => true,
            ));

            $builder->addEventSubscriber(new AddPaisFieldSubscriber($propertyPathToLoalidad))
                    ->addEventSubscriber(new AddProvinciaFieldSubscriber($propertyPathToLoalidad))
                    ->addEventSubscriber(new AddLocalidadFieldSubscriber($propertyPathToLoalidad))
                    // datos de conexion
                    ->add('proveedor', new DepartamentoProveedorType())
                    ->add('ipPrincipal', null, array('label' => 'Principal:', 'required' => false, 'attr' => array('class' => 'ip_address')))
                    ->add('ipRespaldo', null, array('label' => 'Secundario:', 'required' => false, 'attr' => array('class' => 'ip_address')));
        }
        $builder->add('deletedAt', null, array('widget' => 'single_text',
            'attr' => array('class' => 'hidden')));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'configbundle_ubicacion';
    }

}