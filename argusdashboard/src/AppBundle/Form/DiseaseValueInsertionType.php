<?php
/**
 * Disease Value Insertion Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DiseaseValueInsertionType extends ConfigurationAbstractType
{
    protected $valueIsDisabled;

    public function __construct($locales) {
        parent::__construct($locales);
        $this->valueIsDisabled = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $periodOptions = array(
            'label' => 'Configuration.FormItems.Common.Period.Label',
            'translation_domain' => 'configuration_labels'
        );
        $this->_appendPeriodChoiceToOptions($periodOptions);

        $typeOptions = array(
            'label' => 'Configuration.FormItems.Common.Type.Label',
            'translation_domain' => 'configuration_labels',
            'required' => true
        );
        $this->_appendTypeChoiceToOptions($typeOptions);

        $builder->add('id',             IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Common.Id.Label', array('disabled' => true)));
        $builder->add('value',             TextType::class,     $this->_getFieldOptions('Configuration.FormItems.DiseaseValue.Value.Label', array('disabled' => $this->valueIsDisabled)));
        $builder->add('period',          ChoiceType::class,     $periodOptions);
        $builder->add('position',       IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.DiseaseValue.Position.Label', array('required' => true, 'attr' => array('min' => 1, 'max' => 100))));
        $builder->add('type',            ChoiceType::class,     $typeOptions);
        $builder->add('keyWord',            TextType::class,     $this->_getFieldOptions('Configuration.FormItems.DiseaseValue.KeyWord.Label', array('required' => true)));
        $builder->add('mandatory',     CheckboxType::class,     $this->_getFieldOptions('Configuration.FormItems.DiseaseValue.Mandatory.Label', array('required' => false)));
    }

    public function getName()
    {
        return 'app_disease_value_insertion';
    }
}