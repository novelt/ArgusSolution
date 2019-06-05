<?php
/**
 * Site Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use AppBundle\Entity\SesDashboardSite;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Doctrine\ORM\EntityRepository;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SiteInsertionType extends ConfigurationAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$parentIdOptions = $this->_getFieldOptions('Configuration.FormItems.Common.Parent.Label');
        $parentIdOptions['class'] = SesDashboardSite::class;
        $parentIdOptions['property'] = 'path';
        $parentIdOptions['multiple'] = false;

        // Paths must be sorted in the select box
        $parentIdOptions['query_builder'] = function(EntityRepository $repository) use (&$opts) {
            return $repository->createQueryBuilder('s')->orderBy('s.path');
        };*/

        $builder->add(
            'id',
            IntegerType::class,
            $this->_getFieldOptions(
                'Configuration.FormItems.Common.Id.Label',
                ['disabled' => true]
            )
        );
        $builder->add('reference', TextType::class, $this->_getFieldOptions('Configuration.FormItems.Common.Reference.Label'));
        $builder->add('name', TextType::class,    $this->_getFieldOptions('Configuration.FormItems.Common.Name.Label', ['mapped' => false]));

        $builder->add('parent', Select2EntityType::class, [
            'multiple' => false,
            'remote_route' => 'configuration_site_get_all',
            'class' => SesDashboardSite::class,
            'primary_key' => 'id',
            'text_property' => 'name',
            'minimum_input_length' => 0,
            'page_limit' => 10,
            'allow_clear' => false,
            'delay' => 250,
            'cache' => true,
            'cache_timeout' => 60000, // if 'cache' is true
            'language' => $this
                ->_getLocalesSupportedAsString(),
            'placeholder' => 'Configuration.Select2.Choose.Site',
            'scroll' => true,
            'required' => true,
            'label' => 'Configuration.FormItems.Common.Parent.Label',
            'translation_domain' => 'configuration_labels',
            'mapped' => false,
        ]);

        $builder->add('timezone',                       SiteTimezoneType::class, $this->_getFieldOptions('Configuration.FormItems.Site.Timezone.Label'));
        $builder->add('locale',                         SiteLanguageType::class, $this->_getFieldOptions('Configuration.FormItems.Site.Language.Label'));
        $builder->add('longitude',                      NumberType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.Longitude.Label', ['required' => false, 'mapped' => false, 'scale' => 8]));
        $builder->add('latitude',                       NumberType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.Latitude.Label', ['required' => false, 'mapped' => false, 'scale' => 8]));
        $builder->add('alertPreferredGateway',          GatewayDeviceType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.PreferredGateway.Label', array('required' => false)));
        $builder->add('reportDataSourceId',             SiteReportDataSourceType::class,  $this->_getFieldOptions('Configuration.FormItems.Common.ReportDataSource.Label', array('required' => false)));
        $builder->add('overwriteReportDataSourceId',    HiddenType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.OverwriteReportDataSourceCheckbox.Label', ['required' => false, 'mapped' => false]));
        $builder->add('cascadingAlert',                 CheckboxType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.CascadingAlert.Label', array('required' => false)));
        $builder->add('weeklyTimelinessMinutes',        IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Site.WeeklyTimelinessMinutes.Label'));
        $builder->add('monthlyTimelinessMinutes',       IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Site.MonthlyTimelinessMinutes.Label'));
    }

    public function getName()
    {
        return 'app_site_insertion';
    }
}