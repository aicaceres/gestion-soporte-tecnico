<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CompraType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formOptions = array(
            'class'         => 'ConfigBundle:Departamento',
            'placeholder'   => 'Seleccione..',
            'label'         => 'Area Req.:',
            'choice_label'  => 'nombreCompleto',
            'required'      => false,
            'attr'          => array('class'=>'select2')
        );
        $razonOptions = array(
            'class'         => 'ConfigBundle:Ubicacion',
            'placeholder'   => 'Seleccionar',
            'label'         => 'Razón Social:',
            'choice_label'  => 'abreviatura',
            'required'      => false,  
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('d')
                    ->where('d.razonSocial = 1');
                return $qb;
            }
        );

        $builder
            ->add('descripcion',null,array('label'=>'Observaciones:', 'required' => false))    
            ->add('nroFactura',null,array('label'=>'N° Factura:', 'required' => false))    
            ->add('nroRemito',null,array('label'=>'N° Remito:', 'required' => false))    
            ->add('solicitante', 'entity', $formOptions) 
            ->add('razonSocial', 'entity', $razonOptions) 
            ->add('enviado','checkbox',array('mapped' => false,'required' => false))     
            ->add('ordenCompra',null,array('label'=>'N° de Orden:', 'required' => true))  
            ->add('fechaCompra','date',array('widget' => 'single_text', 'label'=>'Fecha Compra:',
                'format' => 'dd-MM-yyyy', 'required' => true))
            ->add('total')    
->add('savenew','hidden',array('mapped' => false,'required' => false))
            ->add('proveedor','entity',array('label'=>'Proveedor:',
                'class' => 'AppBundle:Proveedor', 'required' =>false,
                'attr'  => array('class' => 'select2')))
  
            ->add('detalles', 'collection', array(
                'type'           => new CompraDetalleType(),
                'by_reference'   => false,
                'allow_delete'   => true,
                'allow_add'      => true,
                'prototype_name' => 'items',
                'attr'           => array(
                    'class' => 'row item'
                )))
                                
        ;
    }    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compra'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compra';
    }
}
