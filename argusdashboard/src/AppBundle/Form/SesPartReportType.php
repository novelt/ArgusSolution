<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 8/3/2015
 * Time: 4:07 PM
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SesPartReportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // for the full reference of options defined by each form field type
        // see http://symfony.com/doc/current/reference/forms/types.html
        $builder
            //->add('disease',  null, array('widget' => 'single_text'))
            ->add('reports',    'collection', array
                                                    ('type' =>  new SesReportType(),
                                                     'allow_add' => false,
                                                     'allow_delete' => false ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\SesPartReport',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'SesPartReportForm';
    }
}