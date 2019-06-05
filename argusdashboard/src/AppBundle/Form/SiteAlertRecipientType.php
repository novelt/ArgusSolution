<?php
/**
 * Site Alert Recipient Type
 *
 * @author fc
 */

namespace AppBundle\Form;

use AppBundle\Entity\SesDashboardSite;
use Symfony\Component\Form\FormBuilderInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SiteAlertRecipientType extends ConfigurationAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('recipientSite', Select2EntityType::class, [
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
            'placeholder' => 'Configuration.Select2.Choose.Site',
            'scroll' => true,
            'required' => true,
            'label' => 'Configuration.FormItems.SiteAlertRecipient.RecipientPath.Label',
            'translation_domain' => 'configuration_labels'
        ]);

    }

    public function getName()
    {
        return 'app_site_alert_recipient';
    }
}