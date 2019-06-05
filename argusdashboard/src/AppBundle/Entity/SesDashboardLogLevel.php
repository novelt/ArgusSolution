<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 11/01/2017
 * Time: 15:40
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardLogLevelRepository")
 * @ORM\Table(name="sesdashboard_loglevel", options={"collate"="utf8_general_ci"})
 */
class SesDashboardLogLevel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $code;

    /**
     * @var SesDashboardLog[]
     * @ORM\OneToMany(targetEntity="SesDashboardLog", mappedBy="logLevel")
     */
    private $logs;

    public function __construct() {
        $this->fillCreationDate();
    }

    private function fillCreationDate()
    {
        if (!isset($this->creationDate)) {
            $this->creationDate = new \DateTime("now");
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return SesDashboardLog[]
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param SesDashboardLog[] $logs
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;
    }
}