<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 14/11/2016
 * Time: 17:53
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardIndicatorDimDateTypeRepository")
 * @ORM\Table(name="sesdashboard_indicatordimdatetype", options={"collate"="utf8_general_ci"})
 */
class SesDashboardIndicatorDimDateType extends BaseEntity
{
    const CODE_DAILY = "Daily";
    const CODE_WEEKLY = "Weekly";
    const CODE_WEEKLY_EPIDEMIOLOGIC = "WeeklyEpidemiologic";
    const CODE_MONTHLY = "Monthly";
    const CODE_YEARLY = "Yearly";
    const CODE_CUSTOM = "Custom";

    /**
     * @var string
     * @ORM\Column(type="text", length=50)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="text", length=50)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="text", name="`desc`")
     */
    private $desc;

    public function __construct()
    {
        parent::__construct();
    }

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
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @param string $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }
}