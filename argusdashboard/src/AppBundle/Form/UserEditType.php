<?php
namespace AppBundle\Form;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Form\FormBuilderInterface;


class UserEditType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // Redefine 'required' => false : If password is empty , it is not updated
        $builder->add('plainPassword', 'password', ['label' => 'User.Password', 'required' => false, 'translation_domain' => 'security']);
    }

    public function getName()
    {
        return parent::getName() . 'edit';
    }
}