<?php
/**
 * Taks: Load XML
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnyListXmlLoaderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            FileType::class,
            ['label' => $options['file_field_label']]
        );
        $builder->add(
            'load',
            SubmitType::class,
            ['label' => 'Load']
        );
        $builder->add(
            'cancel',
            SubmitType::class,
            [
                'label' => 'Cancel',
                'attr' => ['formnovalidate' => 'formnovalidate']
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'file_field_label' => 'XML file',
        ));
    }

    public function getName()
    {
        return 'app_any_list_xml_loader';
    }
}