<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Validator\Constraints as Assert;


class GenerationAbstractType extends ConfigurationAbstractType
{
    public function getInputInformationByValue($value)
    {
        switch ($value->getDatatype())
        {
            case 'String':
                $type = TextType::class;
                $constraints = $value->getMandatory() ? new Assert\NotBlank() : null;
                $data = '';
                break;

            case 'Date':
                $type = DateType::class;
                $constraints = new Assert\LessThanOrEqual('today');
                $data = new \DateTime();
                break;

            case 'Integer':
            default:
                $type = IntegerType::class;
                $constraints = new Assert\GreaterThanOrEqual(0);
                $data = 0;
                break;
        }

        return [
            'type' => $type,
            'constraints' => $constraints,
            'data' => $data
        ];
    }

    public function buildFormValues($builder, $prefix, $diseaseValues, $translator = null)
    {
        foreach ($diseaseValues AS $diseaseValue)
        {
            $data = $this->getInputInformationByValue($diseaseValue);

            if ($prefix == 'alert')
            {
                $builder->add('alert_' . $diseaseValue->getId(), $data['type'], [
                    'label' => ucfirst($diseaseValue->getValue()),
                    'constraints' => $data['constraints'],
                    'data' => $data['data'],
                    'required' => $diseaseValue->getMandatory() ? true : false
                ]);
            }
            else
            {
                $disease = $diseaseValue->getParentDisease();

                $builder->add($disease->getId() . '_disease_' . $diseaseValue->getId(), $data['type'], [
                    'label' => $translator->trans('Configuration.FormItems.Generate.Report.Disease', ['%disease_name%' => $disease->getName(), '%value%' => ucfirst($diseaseValue->getValue())], self::TRANSLATION_DOMAIN),
                    'constraints' => $data['constraints'],
                    'data' => $data['data'],
                    'required' => $diseaseValue->getMandatory() ? true : false
                ]);
            }
        }
    }
}
