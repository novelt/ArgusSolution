<?php
/**
 * Contact Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use AppBundle\Entity\SesDashboardContactType;
use AppBundle\Entity\SesDashboardSite;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;


class ContactInsertionType extends ConfigurationAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $siteIdOptions = $this->_getFieldOptions('Configuration.FormItems.Contact.Site.Label');
        $siteIdOptions['class'] = SesDashboardSite::class;
        $siteIdOptions['property'] = 'path';
        $siteIdOptions['multiple'] = false;

        // Paths must be sorted in the select box
        $siteIdOptions['query_builder'] = function(EntityRepository $repository) use (&$opts) {
            return $repository->createQueryBuilder('s')->orderBy('s.path');
        };

        $contactTypeOptions = $this->_getFieldOptions('Configuration.FormItems.Contact.Type.Label');
        $contactTypeOptions['class'] = SesDashboardContactType::class ;
        $contactTypeOptions['property'] = 'name';
        $contactTypeOptions['multiple'] = false;
        $contactTypeOptions['required'] = false;
        $contactTypeOptions['placeholder'] = '';
        $contactTypeOptions['empty_data'] = null;

        $builder->add('id',             IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Common.Id.Label', array('disabled' => true)));
        $builder->add('name',           TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Common.Name.Label'));
        $builder->add('site', Select2EntityType::class, [
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
            'label' => 'Configuration.FormItems.Contact.Site.Label',
            'translation_domain' => 'configuration_labels'
        ]);
        $builder->add('phoneNumber',        TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Contact.PhoneNumber.Label'));
        $builder->add('contactType',        EntityType::class,      $contactTypeOptions);
        $builder->add('imei',               TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Contact.Imei.Label', array('required' => false)));
        $builder->add('imei2',              TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Contact.Imei2.Label', array('required' => false)));
        $builder->add('email',              EmailType::class,       $this->_getFieldOptions('Configuration.FormItems.Contact.Email.Label', array('required' => false)));
        $builder->add('alertPreferredGateway',GatewayDeviceType::class,  $this->_getFieldOptions('Configuration.FormItems.Contact.PreferredGateway.Label', array('required' => false)));
        $builder->add('note',               TextareaType::class,  $this->_getFieldOptions('Configuration.FormItems.Common.Note.Label', array('required' => false)));
    }

    public function getName()
    {
        return 'app_contact_insertion';
    }
}