<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 23/02/2017
 * Time: 16:09
 */

namespace AppBundle\Form;

use AppBundle\Entity\SesDashboardReportDataSource;
use AppBundle\Services\ReportDataSourceService;
use AppBundle\Services\Timezone\TimezoneService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class DiseaseReportDataSourceType extends ReportDataSourceType
{
    public function __construct($locales, TranslatorInterface $translator, ReportDataSourceService $reportDataSourceService)
    {
        parent::__construct($locales, $translator, $reportDataSourceService);
    }

    /*public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('timezone', ChoiceType::class, [
            'choices' => $this->getTimezones()
        ]);
    }*/

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->buildReportDataSources(false),
            'choices_as_values' => false,
            'choice_translation_domain' => false
        ));
    }
}