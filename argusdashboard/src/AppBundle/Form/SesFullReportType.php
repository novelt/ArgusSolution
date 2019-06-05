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

use AppBundle\Utils\Helper;


class SesFullReportType extends AbstractType
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
            ->add('period', 'choice', array(
                'choices' => array(
                    'Weekly'  => 'Weekly',
                    'Monthly' => 'Monthly',
                ),
                /*'expanded'  => true,
                'multiple'  => false,*/
                'disabled' => true,
                'label' => 'Period'
            ))
           ->add('periodHidden', 'hidden', array(
                                            'property_path' => 'period'
           ))
            ->add('startDate', 'date', array(
                                        'input'  => 'datetime',
                                        'label' => 'Start date'
            ))
            ->add('partReports',    'collection', array
                                                    ('type' =>  new SesPartReportType(),
                                                     'allow_add' => false,
                                                     'allow_delete' => false ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $product = $event->getData();
            $form = $event->getForm();

            $form
                ->add('siteName', 'text', array(
                                        'mapped' => false,
                                        'disabled' => true,
                                        'data' => $product->getFrontLineGroup()->getName(),
                                        'label' => 'Site'

                ))
                ->add('siteHidden', 'hidden', array(
                                        'mapped' => false,
                                        'data' => Helper::encryptString($product->getFrontLineGroup()->getPath())
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
            'data_class' => 'AppBundle\Entity\SesFullReport',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'SesFullReportForm';
    }
}