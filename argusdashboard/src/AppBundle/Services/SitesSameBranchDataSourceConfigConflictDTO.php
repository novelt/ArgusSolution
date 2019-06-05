<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 07-Aug-18
 * Time: 16:03
 */

namespace AppBundle\Services;


use AppBundle\Entity\SesDashboardReportDataSource;
use AppBundle\Entity\SesDashboardSite;

class SitesSameBranchDataSourceConfigConflictDTO
{
    /**
     * @var bool
     */
    private $conflict;

    /**
     * @var SesDashboardReportDataSource
     */
    private $reportDataSource;

    /**
     * @var int[]
     */
    private $siteIds;

    /**
     * @var SesDashboardSite[]
     */
    private $sites;

    /**
     * @var string
     */
    private $message;

    /**
     * @return bool
     */
    public function isConflict()
    {
        return $this->conflict;
    }

    /**
     * @param bool $conflict
     */
    public function setConflict($conflict)
    {
        $this->conflict = $conflict;
    }

    /**
     * @return int[]
     */
    public function getSiteIds()
    {
        return $this->siteIds;
    }

    /**
     * @param int[] $siteIds
     */
    public function setSiteIds($siteIds)
    {
        $this->siteIds = $siteIds;
    }

    /**
     * @return SesDashboardSite[]
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param SesDashboardSite[] $sites
     */
    public function setSites($sites)
    {
        $this->sites = $sites;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return SesDashboardReportDataSource
     */
    public function getReportDataSource()
    {
        return $this->reportDataSource;
    }

    /**
     * @param SesDashboardReportDataSource $reportDataSource
     */
    public function setReportDataSource($reportDataSource)
    {
        $this->reportDataSource = $reportDataSource;
    }
}