<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Validator\Constraints as Assert;

class GenerateMonthlyReportType extends ConfigurationAbstractType
{
    const INCLUDE_ALERTS = false;

    public function __construct($locales)
    {
        parent::__construct($locales);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['disease_value_service', 'translator']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $diseaseValueService = $options['disease_value_service'];
        $translator = $options['translator'];

        $builder->add('year', IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Generate.Report.Year', [
            'data' => date('Y'),
            'constraints' => new Assert\Range(['min' => 1970, 'max' => (int) date('Y')]),
            'required' => true
        ]));

        $builder->add('month', IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Generate.Report.Month', [
            'data' => date('m'),
            'constraints' => new Assert\Range(['min' => 1, 'max' => 12]),
            'required' => true
        ]));

        $builder->add('contact', 'entity', $this->_getFieldOptions('Configuration.FormItems.Generate.Report.Contact', [
            'class' => 'AppBundle:SesDashboardContact',
            'property' => function($contact) { return sprintf('%s (%s)', $contact->getSite()->getName(), $contact->getPhoneNumber()); },
            'required' => true
        ]));

        foreach ($diseaseValueService->getDiseaseValuesByPeriod('monthly') AS $value)
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

            $disease = $value->getParentDisease();

            $builder->add($disease->getId() . '_disease_' . $value->getId(), $type, [
                'label' => $translator->trans('Configuration.FormItems.Generate.Report.Disease', ['%disease_name%' => $disease->getName(), '%value%' => ucfirst($value->getValue())], self::TRANSLATION_DOMAIN),
                'constraints' => $constraints,
                'data' => $data,
                'required' => $value->getMandatory() ? true : false
            ]);
        }
    }

    public function getName()
    {
        return 'app_generate_report';
    }
}
