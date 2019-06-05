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

abstract class ReportDataSourceType extends ConfigurationAbstractType
{
    /**
     * Stores the available timezone choices.
     *
     * @var SesDashboardReportDataSource[]
     */
    private $reportDataSources;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TimezoneService
     */
    private $reportDataSourceService;

    public function __construct($locales, TranslatorInterface $translator, ReportDataSourceService $reportDataSourceService)
    {
        parent::__construct($locales);
        $this->translator = $translator;
        $this->reportDataSourceService = $reportDataSourceService;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    protected function buildReportDataSources($displayInheritedLabel) {
        if($this->reportDataSources === null) {
            $this->reportDataSources = [];
            if($displayInheritedLabel) {
                $inheritedTranslationString = $this->translator->trans('Configuration.FormItems.Common.ReportDataSource.InheritedValue', array(), ConfigurationAbstractType::TRANSLATION_DOMAIN);
            }
            else {
                $inheritedTranslationString = "";
            }

            $this->reportDataSources[""] = $inheritedTranslationString;

            foreach ($this->reportDataSourceService->findAll() as $dataSource) {
                $this->reportDataSources[$dataSource->getId()] = $dataSource->getName();
            }
        }

        return $this->reportDataSources;
    }

    /**
     * @return array
     */
    public function getReportDataSources()
    {
        return $this->reportDataSources;
    }

    /**
     * @param array $reportDataSources
     */
    public function setReportDataSources($reportDataSources)
    {
        $this->reportDataSources = $reportDataSources;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return TimezoneService
     */
    public function getReportDataSourceService()
    {
        return $this->reportDataSourceService;
    }

    /**
     * @param TimezoneService $reportDataSourceService
     */
    public function setReportDataSourceService($reportDataSourceService)
    {
        $this->reportDataSourceService = $reportDataSourceService;
    }
}