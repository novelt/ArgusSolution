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

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Validator\Constraints\NotBlank;

class SesReportValuesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // for the full reference of options defined by each form field type
        // see http://symfony.com/doc/current/reference/forms/types.html

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $product = $event->getData();
            $form = $event->getForm();

            $form
            ->add('Value', 'integer', array(
                                    'required' => false,
                                    'label' => $product->getKey(),
                                    'constraints' => array(
                                                        new NotBlank(
                                                            array('message' => '*')
                                                        ))
            ))
            ;

        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\SesReportValues',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'SesReportValuesForm';
    }
}