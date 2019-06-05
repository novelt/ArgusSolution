<?php
/**
 * Threshold Insertion Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\SesDashboardDiseaseValue;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Form\Transformer\SesDashboardDiseaseValueTransformer;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;


class ThresholdInsertionType extends ConfigurationAbstractType
{
    protected function _getSiteIdOptions()
    {
        $siteIdOptions = $this->_getFieldOptions('Configuration.FormItems.Threshold.Site.Label');
        $siteIdOptions['class'] = SesDashboardSite::class;
        $siteIdOptions['property'] = 'path';
        $siteIdOptions['multiple'] = false;

        // Paths must be sorted in the select box
        $siteIdOptions['query_builder'] = function(EntityRepository $repository) use (&$opts) {
            return $repository->createQueryBuilder('s')->orderBy('s.path');
        };

        return $siteIdOptions;
    }

    protected function _getDiseaseValueIdOptions()
    {
        $diseaseValueIdOptions = $this->_getFieldOptions('Configuration.FormItems.Threshold.DiseaseValue.Label');
        $diseaseValueIdOptions['class'] = SesDashboardDiseaseValue::class;
        $diseaseValueIdOptions['property'] = 'value';
        $diseaseValueIdOptions['multiple'] = false;

        // Paths must be sorted in the select box
        $diseaseValueIdOptions['query_builder'] = function(EntityRepository $repository) use (&$opts) {
            return $repository->createQueryBuilder('dv')->orderBy('dv.value');
        };

        return $diseaseValueIdOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $siteIdOptions = $this->_getSiteIdOptions();
        $diseaseValueIdOptions = $this->_getDiseaseValueIdOptions();

        $periodOptions = array(
            'label' => 'Configuration.FormItems.Common.Period.Label',
            'translation_domain' => 'configuration_labels'
        );
        $this->_appendPeriodChoiceToOptions($periodOptions);

        $builder->add('id',             IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Common.Id.Label', array('disabled' => true)));
        if ($siteIdOptions) {
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
                'required' => true,
                'label' => 'Configuration.FormItems.Threshold.Site.Label',
                'translation_domain' => 'configuration_labels'

            ]);
        } else {
            $builder->add('siteReference',      TextType::class,    $this->_getFieldOptions('Configuration.FormItems.Threshold.SiteReference.Label', array('disabled' => true)));
        }
        if ($diseaseValueIdOptions) {
            $builder->add('diseaseValue', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'configuration_diseaseValue_get_all',
                'class' => SesDashboardDiseaseValue::class,
                'primary_key' => 'id',
                'transformer' => SesDashboardDiseaseValueTransformer::class,
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000, // if 'cache' is true
                'language' => $this
                    ->_getLocalesSupportedAsString(),
                'placeholder' => 'Configuration.Select2.Choose.Disease&Value',
                'required' => true,
                'label' => 'Configuration.FormItems.Threshold.Disease&Value.Label',
                'translation_domain' => 'configuration_labels'
            ]);
        } else {
            $builder->add('diseaseValue',   TextType::class,    $this->_getFieldOptions('Configuration.FormItems.Threshold.DiseaseValue.Label', array('disabled' => true)));
        }
        $builder->add('period',          ChoiceType::class,     $periodOptions);
        $builder->add('weekNumber',     IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Threshold.WeekNumber.Label', array('required' => false)));
        $builder->add('monthNumber',    IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Threshold.MonthNumber.Label', array('required' => false)));
        $builder->add('year',           IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Threshold.Year.Label'));
        $builder->add('maxValue',       IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Threshold.MaxValue.Label'));
    }

    public function getName()
    {
        return 'app_threshold_insertion';
    }
}