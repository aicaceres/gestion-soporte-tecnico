<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nroReferencia',null,array('label' => 'N° Referencia:'))
            ->add('fecha','date',array('widget' => 'single_text','label'=>'Fecha:',
                'format' => 'dd-MM-yyyy', 'required' => true)) 
            ->add('referente',null,array('label' => 'Referente:'))            
            ->add('proveedor')
            ->add('detalle',null,array('label' => 'Observaciones:'))
            ->add('abierto','choice', array('choices' => array( '1'=>'Abierto',
                '0'=>'Cerrado'), 'label'=>'Estado:'))                                        
            ->add('auxiliar','textarea',array('label' => 'Detalle:','mapped' => false, 'attr'=>array('rows'=>1)));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Reclamo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_reclamo';
    }
}
