<?php
/**
 * Disease Value Edition Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Form\DiseaseValueInsertionType;

class DiseaseValueEditionType extends DiseaseValueInsertionType
{
    public function __construct($locales) {
        parent::__construct($locales);
        $this->valueIsDisabled = true;
    }

    public function getName()
    {
        return 'app_disease_value_edition';
    }
}