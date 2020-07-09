<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateAlertType extends GenerationAbstractType
{
    public function __construct($locales)
    {
        parent::__construct($locales);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('disease_service');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $diseaseService = $options['disease_service'];

        $builder->add('contact', 'entity', $this->_getFieldOptions('Configuration.FormItems.Generate.Alert.PhoneNumber', [
            'class' => 'AppBundle:SesDashboardContact',
            'property' => 'phone_number',
            'required' => true
        ]));

        $alertDisease = $diseaseService->getAlertDisease();
        $this->buildFormValues($builder, 'alert', $alertDisease->getDiseaseValues());
    }

    public function getName()
    {
        return 'app_generate_alert';
    }
}
