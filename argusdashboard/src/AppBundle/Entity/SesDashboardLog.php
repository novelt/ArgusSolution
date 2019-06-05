<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 11/01/2017
 * Time: 15:37
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardLogRepository")
 * @ORM\Table(
 *      name="sesdashboard_log",
 *     options={"collate"="utf8_general_ci"},
 *     indexes={
 *      @ORM\Index(name="sesdashboard_log_calculationSessionId_idx", columns={"calculationSessionId"}),
 *  }
*  )
 */
class SesDashboardLog extends BaseEntity
{
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $source;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $logLevelId;

    /**
     * @var SesDashboardLogLevel
     * @ORM\ManyToOne(targetEntity="SesDashboardLogLevel", inversedBy="logs")
     * @ORM\JoinColumn(name="logLevelId", referencedColumnName="id")
     */
    private $logLevel;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $calculationSessionId;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getLogLevelId()
    {
        return $this->logLevelId;
    }

    /**
     * @param int $logLevelId
     */
    public function setLogLevelId($logLevelId)
    {
        $this->logLevelId = $logLevelId;
    }

    /**
     * @return SesDashboardLogLevel
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param SesDashboardLogLevel $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
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
     * @return int
     */
    public function getCalculationSessionId()
    {
        return $this->calculationSessionId;
    }

    /**
     * @param int $calculationSessionId
     */
    public function setCalculationSessionId($calculationSessionId)
    {
        $this->calculationSessionId = $calculationSessionId;
    }
}