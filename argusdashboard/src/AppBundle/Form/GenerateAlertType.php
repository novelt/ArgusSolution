<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Symfony\Component\Validator\Constraints as Assert;

class GenerateAlertType extends ConfigurationAbstractType
{
    public function __construct($locales)
    {
        parent::__construct($locales);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['disease_service', 'translator']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $diseaseService = $options['disease_service'];
        $translator = $options['translator'];

        $alertDisease = $diseaseService->getAlertDisease();
        foreach ($alertDisease->getDiseaseValues() AS $diseaseValue)
        {
            $data = $this->getInputDataFromType($diseaseValue->getDatatype(), $diseaseValue);

            $builder->add('alert_' . $diseaseValue->getId(), $data['type'], [
                'label' => ucfirst($diseaseValue->getValue()),
                'constraints' => $data['constraints'],
                'data' => $data['data'],
                'required' => $diseaseValue->getMandatory() ? true : false
            ]);
        }

        /*$builder->add('disease', 'entity', $this->_getFieldOptions('Configuration.FormItems.Generate.Alert.Disease', [
            'class' => 'AppBundle:SesDashboardDisease',
            'placeholder' => $translator->trans('Configuration.FormItems.Generate.Alert.SelectDisease', [], self::TRANSLATION_DOMAIN),
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('d')
                    ->where('d.disease != :disease')
                    ->setParameter('disease', \AppBundle\Entity\Constant::DISEASE_ALERT);
            },
            'required' => true
        ]));

        $builder->get('disease')->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($diseaseService)
            {
                $form = $event->getForm();

                $disease = $diseaseService->getById($event->getData());
                if ($disease)
                {
                    foreach($disease->getDiseaseValues() AS $diseaseValue)
                    {
                        $data = $this->getInputDataFromType($diseaseValue->getDatatype(), $diseaseValue);

                        $form->getParent()->add('value_' . $diseaseValue->getId(), $data['type'], [
                            'label' => ucfirst($diseaseValue->getValue()),
                            'constraints' => $data['constraints'],
                            'data' => $data['data'],
                            'required' => $diseaseValue->getMandatory() ? true : false
                        ]);
                    }

                    //$form->getParent()->get('disease')->getConfig()->setAttribute('read_only', true);
                }
            }
        );*/
    }

    private function getInputDataFromType($type, $value)
    {
        $data = [];

        switch ($type)
        {
            case 'String':
                $data['type'] = TextType::class;
                $data['constraints'] = $value->getMandatory() ? new Assert\NotBlank() : null;
                $data['data'] = '';
                break;

            case 'Date':
                $data['type'] = DateType::class;
                $data['constraints'] = new Assert\LessThanOrEqual('today');
                $data['data'] = new \DateTime();
                break;

            case 'Integer':
            default:
                $data['type'] = IntegerType::class;
                $data['constraints'] = new Assert\GreaterThanOrEqual(0);
                $data['data'] = 0;
                break;
        }

        return $data;
    }

    public function getName()
    {
        return 'app_generate_alert';
    }
}
