<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 4/1/2016
 * Time: 12:00 PM
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Security\SesDashboardPermissionAction as Action;
use AppBundle\Entity\Security\SesDashboardPermissionRessource as Ressource;
use AppBundle\Entity\Security\SesDashboardPermissionState as State;
use AppBundle\Entity\Security\SesDashboardPermissionType as Type;
use AppBundle\Entity\Security\SesDashboardPermissionScope as Scope;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dashboardRoleId', 'hidden')
            ->add('id', 'hidden')
            ->add('ressource', ChoiceType::class, ['label' => 'Permission.Ressource', 'choices' => [
                            Ressource::RESSOURCE_ANY => 'Permission.Choices.'.Ressource::RESSOURCE_ANY,
                            Ressource::RESSOURCE_ALERT => 'Permission.Choices.'.Ressource::RESSOURCE_ALERT,
                            Ressource::RESSOURCE_WEEKLY_REPORT => 'Permission.Choices.'.Ressource::RESSOURCE_WEEKLY_REPORT,
                            Ressource::RESSOURCE_MONTHLY_REPORT => 'Permission.Choices.'.Ressource::RESSOURCE_MONTHLY_REPORT,
                            Ressource::RESSOURCE_DASHBOARD_REPORT => 'Permission.Choices.'.Ressource::RESSOURCE_DASHBOARD_REPORT,
                            ],
                                'choice_translation_domain' => 'security', 'translation_domain' => 'security'])

            ->add('action', ChoiceType::class, ['label' => 'Permission.Action', 'choices' => [
                Action::ACTION_VIEW => 'Permission.Choices.'.Action::ACTION_VIEW,
                Action::ACTION_VALIDATE => 'Permission.Choices.'.Action::ACTION_VALIDATE,
                Action::ACTION_REJECT => 'Permission.Choices.'.Action::ACTION_REJECT,
                Action::ACTION_DOWNLOAD=> 'Permission.Choices.'.Action::ACTION_DOWNLOAD,
                Action::ACTION_UPLOAD=> 'Permission.Choices.'.Action::ACTION_UPLOAD
                ],
                'choice_translation_domain' => 'security', 'translation_domain' => 'security'])

            ->add('state', ChoiceType::class, ['label' => 'Permission.State', 'choices' => [
                            State::STATE_ANY => 'Permission.Choices.'.State::STATE_ANY,
                            State::STATE_PENDING => 'Permission.Choices.'.State::STATE_PENDING,
                            State::STATE_VALIDATED => 'Permission.Choices.'.State::STATE_VALIDATED,
                            State::STATE_REJECTED => 'Permission.Choices.'.State::STATE_REJECTED
                            ],
                                'choice_translation_domain' => 'security', 'translation_domain' => 'security'])
            ->add('type', ChoiceType::class, ['label' => 'Permission.Type', 'choices' => [
                            Type::TYPE_ALLOW => 'Permission.Choices.'.Type::TYPE_ALLOW,
                            Type::TYPE_DENY => 'Permission.Choices.'.Type::TYPE_DENY],
                                'choice_translation_domain' => 'security', 'translation_domain' => 'security'])
            ->add('scope', ChoiceType::class, ['label' => 'Permission.Scope', 'choices' => [
                            Scope::SCOPE_ALL => 'Permission.Choices.'.Scope::SCOPE_ALL,
                            Scope::SCOPE_SINGLE => 'Permission.Choices.'.Scope::SCOPE_SINGLE],
                                'choice_translation_domain' => 'security', 'translation_domain' => 'security'])
             ->add('level', ChoiceType::class, ['label' => 'Permission.Level', 'choices' => [
                            -10 => '-10',
                            -9 => '-9',
                            -8 => '-8',
                            -7 => '-7',
                            -6 => '-6',
                            -5 => '-5',
                            -4 => '-4',
                            -3 => '-3',
                            -2 => '-2',
                            -1 => '-1',
                            0 => '0',
                            1 => '1',
                            2 => '2',
                            3 => '3',
                            4 => '4',
                            5 => '5',
                            6 => '6',
                            7 => '7',
                            8 => '8',
                            9 => '9',
                            10 => '10'],
                            'choice_translation_domain' => 'security', 'translation_domain' => 'security']);

        /*$addActionListener = function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            switch($data['ressource']){
                case Ressource::RESSOURCE_ANY :
                    $choices = [
                        Action::ACTION_VIEW => 'Permission.Choices.'.Action::ACTION_VIEW,
                        Action::ACTION_VALIDATE => 'Permission.Choices.'.Action::ACTION_VALIDATE,
                        Action::ACTION_REJECT => 'Permission.Choices.'.Action::ACTION_REJECT,
                        Action::ACTION_DOWNLOAD=> 'Permission.Choices.'.Action::ACTION_DOWNLOAD];
                    break;
                case Ressource::RESSOURCE_WEEKLY_REPORT :
                    $choices = [
                        Action::ACTION_VIEW => 'Permission.Choices.'.Action::ACTION_VIEW,
                        Action::ACTION_VALIDATE => 'Permission.Choices.'.Action::ACTION_VALIDATE,
                        Action::ACTION_REJECT => 'Permission.Choices.'.Action::ACTION_REJECT];
                    break;
            }

            $form->add('action',ChoiceType::class, array('label' => 'Permission.Action',
                'choices'=> $choices,
                'choice_translation_domain' => 'security',
                'translation_domain' => 'security'));
        };*/

        // This listener will adapt the form with the data passed to the form during construction :
        // $builder->addEventListener(FormEvents::PRE_SET_DATA, $addActionListener);

        // This listener will adapt the form with the submitted data :
        //$builder->addEventListener(FormEvents::PRE_SUBMIT, $addActionListener);

    }

    public function getName()
    {
        return 'app_permission';
    }
}