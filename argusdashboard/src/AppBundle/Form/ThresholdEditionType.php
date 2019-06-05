<?php
/**
 * Threshold Insertion Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class ThresholdEditionType extends ThresholdInsertionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'app_threshold_edition';
    }
}