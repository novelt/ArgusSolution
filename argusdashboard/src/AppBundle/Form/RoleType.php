<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/24/2016
 * Time: 2:39 PM
 */

namespace AppBundle\Form;

use AppBundle\Entity\Security\SesDashboardRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('name', 'text', ['label' => 'Role.Name', 'translation_domain' => 'security']);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SesDashboardRole::class,
        ));
    }

    public function getName()
    {
       return 'app_role';
    }
}