<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Validator\Constraints as Assert;

class GenerateMonthlyReportType extends GenerationAbstractType
{
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

        $this->buildFormValues($builder, 'disease', $diseaseValueService->getDiseaseValuesByPeriod('monthly'), $translator);
    }

    public function getName()
    {
        return 'app_generate_report';
    }
}
