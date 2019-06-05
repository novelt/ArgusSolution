<?php
/**
 * Taks: Save XML
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnyListXmlSavingType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('path',         TextType::class, array('data' => $options['configuration_file_path']));
        $builder->add('save',       SubmitType::class, array('label' => 'Save'));
        $builder->add('cancel',     SubmitType::class, array('label' => 'Cancel', 'attr' => array('formnovalidate'=>'formnovalidate')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'configuration_file_path' => '/tmp',
        ));
    }

    public function getName()
    {
        return 'app_any_list_xml_saving';
    }
}