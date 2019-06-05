<?php
/**
 * Disease Constraint Insertion Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DiseaseConstraintType extends ConfigurationAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $operatorOptions = array(
            'label' => 'Configuration.FormItems.Common.Operator.Label',
            'translation_domain' => 'configuration_labels'
        );
        $this->_appendOperatorChoiceToOptions($operatorOptions);

        $periodOptions = array(
            'label' => 'Configuration.FormItems.Common.Period.Label',
            'translation_domain' => 'configuration_labels'
        );
        $this->_appendPeriodChoiceToOptions($periodOptions);

        $builder->add('id',                     IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Common.Id.Label', array('disabled' => true)));
        $builder->add('referenceValueFrom',     TextType::class,        $this->_getFieldOptions('Configuration.FormItems.DiseaseConstraint.ReferenceValueFrom.Label'));
        $builder->add('operator',               ChoiceType::class,      $operatorOptions);
        $builder->add('referenceValueTo',       TextType::class,        $this->_getFieldOptions('Configuration.FormItems.DiseaseConstraint.ReferenceValueTo.Label'));
        $builder->add('period',                 ChoiceType::class,      $periodOptions);
    }

    public function getName()
    {
        return 'app_disease_constraint';
    }
}