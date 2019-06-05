<?php
/**
 * Site Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SiteEditionType extends ConfigurationAbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id',                             IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Common.Id.Label', array('disabled' => true)));
        $builder->add('reference',                      TextType::class,    $this->_getFieldOptions('Configuration.FormItems.Common.Reference.Label', array('disabled' => true)));
        $builder->add('name',                           TextType::class,    $this->_getFieldOptions('Configuration.FormItems.Common.Name.Label', ['mapped' => false]));
        $builder->add('timezone',                       SiteTimezoneType::class, $this->_getFieldOptions('Configuration.FormItems.Site.Timezone.Label'));
        $builder->add('locale',                         SiteLanguageType::class, $this->_getFieldOptions('Configuration.FormItems.Site.Language.Label'));
        $builder->add('longitude',                      NumberType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.Longitude.Label', ['required' => false, 'mapped' => false, 'scale' => 8]));
        $builder->add('latitude',                       NumberType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.Latitude.Label', ['required' => false, 'mapped' => false, 'scale' => 8]));
        //$builder->add('weeklyReminderOverrunMinutes',   IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Site.WeeklyReminderOverrunMinutes.Label'));
        //$builder->add('monthlyReminderOverrunMinutes',  IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Site.MonthlyReminderOverrunMinutes.Label'));
        $builder->add('alertPreferredGateway',          GatewayDeviceType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.PreferredGateway.Label', array('required' => false)));
        $builder->add('reportDataSourceId',             SiteReportDataSourceType::class,  $this->_getFieldOptions('Configuration.FormItems.Common.ReportDataSource.Label', array('required' => false)));
        $builder->add('overwriteReportDataSourceId',    HiddenType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.OverwriteReportDataSourceCheckbox.Label', ['required' => false, 'mapped' => false]));
        $builder->add('cascadingAlert',                 CheckboxType::class,  $this->_getFieldOptions('Configuration.FormItems.Site.CascadingAlert.Label', array('required' => false)));
        $builder->add('weeklyTimelinessMinutes',   IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Site.WeeklyTimelinessMinutes.Label'));
        $builder->add('monthlyTimelinessMinutes',  IntegerType::class, $this->_getFieldOptions('Configuration.FormItems.Site.MonthlyTimelinessMinutes.Label'));
    }

    public function getName()
    {
        return 'app_site_edition';
    }
}