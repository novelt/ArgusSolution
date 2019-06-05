<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 10-Nov-17
 * Time: 13:49
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardReportDataSourceRepository")
 * @ORM\Table(
 *     name="sesdashboard_report_datasource",
 *	   uniqueConstraints={
 *	     @ORM\UniqueConstraint(columns={"code"})
 *	   },
 *     options={"collate"="utf8_general_ci"})
 */
class SesDashboardReportDataSource extends BaseEntity
{
    //These codes have to be maintained with the codes stored in the database
    const CODE_EXCEL = "EXCEL";

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     *
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="string")
     *
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     *
     */
    private $description;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     *
     */
    private $checkConfigurationConflict;

    /**
     * @var SesDashboardSite[]
     * @ORM\OneToMany(targetEntity="SesDashboardSite", mappedBy="reportDataSource")
     */
    private $sites;

    /**
     * @var SesDashboardDisease[]
     * @ORM\OneToMany(targetEntity="SesDashboardDisease", mappedBy="reportDataSource")
     */
    private $diseases;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return SesDashboardDisease[]
     */
    public function getDiseases()
    {
        return $this->diseases;
    }

    /**
     * @param SesDashboardDisease[] $diseases
     */
    public function setDiseases($diseases)
    {
        $this->diseases = $diseases;
    }

    /**
     * @return bool
     */
    public function isCheckConfigurationConflict()
    {
        return $this->checkConfigurationConflict;
    }

    /**
     * @param bool $checkConfigurationConflict
     */
    public function setCheckConfigurationConflict($checkConfigurationConflict)
    {
        $this->checkConfigurationConflict = $checkConfigurationConflict;
    }
}