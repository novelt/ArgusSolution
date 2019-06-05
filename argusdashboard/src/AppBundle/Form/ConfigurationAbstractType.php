<?php
/**
 * Abstract Type for Disease Properties (values or constraints)
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;

class ConfigurationAbstractType extends AbstractType
{
    private $locales;

    function __construct($locales)
    {
        $this->locales = $locales;
    }

    const TRANSLATION_DOMAIN = 'configuration_labels';

    static private $operatorChoices = array(
        'GREATER'           => 'Configuration.FormItems.Common.Operator.Choices.GREATER',
        'GREATER_EQUAL'     => 'Configuration.FormItems.Common.Operator.Choices.GREATER_EQUAL',
        'LESS'              => 'Configuration.FormItems.Common.Operator.Choices.LESS',
        'LESS_EQUAL'        => 'Configuration.FormItems.Common.Operator.Choices.LESS_EQUAL',
        'NOT_EQUAL'         => 'Configuration.FormItems.Common.Operator.Choices.NOT_EQUAL'
    );

    static private $periodChoices = array(
        'Weekly'            => 'Configuration.FormItems.Common.Period.Choices.Weekly',
        'Monthly'           => 'Configuration.FormItems.Common.Period.Choices.Monthly',
        'None'              => 'Configuration.FormItems.Common.Period.Choices.None'
    );

    static private $priorityChoices = array(
        'Primary'           => 'Configuration.FormItems.Common.Priority.Choices.Primary',
        'Secondary'         => 'Configuration.FormItems.Common.Priority.Choices.Secondary'
    );

    static private $typeChoices = array(
        'Integer'           => 'Configuration.FormItems.Common.Type.Choices.Integer',
        'String'            => 'Configuration.FormItems.Common.Type.Choices.String',
        'Date'              => 'Configuration.FormItems.Common.Type.Choices.Date'
    );

    protected function _appendOperatorChoiceToOptions( & $toOptions)
    {
        $toOptions['choices'] = self::$operatorChoices;
        $toOptions['choice_translation_domain'] = self::TRANSLATION_DOMAIN;
    }

    protected function _appendPeriodChoiceToOptions( & $toOptions)
    {
        $toOptions['choices'] = self::$periodChoices;
        $toOptions['choice_translation_domain'] = self::TRANSLATION_DOMAIN;
    }

    protected function _appendPriorityChoiceToOptions( & $toOptions)
    {
        $toOptions['choices'] = self::$priorityChoices;
        $toOptions['choice_translation_domain'] = self::TRANSLATION_DOMAIN;
    }

    protected function _appendTypeChoiceToOptions( & $toOptions)
    {
        $toOptions['choices'] = self::$typeChoices;
        $toOptions['choice_translation_domain'] = self::TRANSLATION_DOMAIN;
    }

    protected function _getFieldOptions($label, $initial = array())
    {
        $initial['label'] = $label;
        $initial['translation_domain'] = 'configuration_labels';
        return $initial;
    }

    protected function _getLocalesSupportedAsString() {
        return '['. implode(',', $this->locales) .']';
    }
}