<?php
namespace AppBundle\Form;

use AppBundle\Entity\Security\SesDashboardRole;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesDashboardSite;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;


class UserType extends ConfigurationAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', ['label' => 'User.FirstName', 'translation_domain' => 'security'])
            ->add('lastName', 'text', ['label' => 'User.LastName', 'translation_domain' => 'security'])
            ->add('username', 'text', ['label' => 'User.UserName', 'translation_domain' => 'security'])
            ->add('email', 'text', ['label' => 'User.Email', 'translation_domain' => 'security'])
            ->add('enabled', 'checkbox', ['label' => 'User.Enabled', 'required' => false, 'translation_domain' => 'security'])
            ->add('plainPassword', 'password', ['label' => 'User.Password', 'required' => true, 'translation_domain' => 'security'])
            ->add('site', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'configuration_site_get_all',
                'class' => SesDashboardSite::class,
                'primary_key' => 'id',
                'text_property' => 'name',
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000, // if 'cache' is true
                'language' => $this
                    ->_getLocalesSupportedAsString(),
                'placeholder' => 'General.Select2.Choose.Site',
                'scroll' => true,
                'required' => true,
                'label' => 'User.RootSite',
                'translation_domain' => 'security'
            ])

            ->add('isAdmin', 'checkbox', ['label' => 'User.AdminRights', 'required' => false, 'translation_domain' => 'security'])
            ->add('dashboardRoles', 'entity', ['label' => 'User.Roles', 'class' => SesDashboardRole::class, 'multiple' => true, 'property' => 'name',  'translation_domain' => 'security']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SesDashboardUser::class,
        ));
    }

    public function getName()
    {
        return 'app_user';
    }
}