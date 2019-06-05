<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 18/01/2018
 * Time: 16:16
 */

namespace AppBundle\Entity\Gateway;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Gateway\GatewayDeviceRepository")
 * @ORM\Table(
 *     name="ses_gateway_devices", options={"collate"="utf8_general_ci"},
 *     indexes={
 *       @ORM\Index(name="ses_gateway_devices_gatewayId_idx", columns={"gatewayId"}),
 *  }
 * )
 */
class GatewayDevice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false)
     */
    private $gatewayId;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default" = null})
     */
    private $operator;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default" = null})
     */
    private $manufacturer;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default" = null})
     */
    private $model;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default" = null})
     */
    private $sdk;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default" = null})
     */
    private $versionName;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default" = null})
     */
    private $version;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" = null})
     */
    private $battery;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" = null})
     */
    private $power;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateDate;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" = null})
     */
    private $pollInterval;

    /**
     * @param $gatewayId
     * @param null $operator
     * @param null $manufacturer
     * @param null $model
     * @param null $sdk
     * @param null $versionName
     * @param null $version
     * @param null $battery
     * @param null $power
     * @param null $pollInterval
     * @return GatewayDevice
     */
    public static function createNewInstance($gatewayId,
                                             $operator = null,
                                             $manufacturer = null,
                                             $model = null,
                                             $sdk = null,
                                             $versionName = null,
                                             $version = null,
                                             $battery = null,
                                             $power = null,
                                             $pollInterval = null)
    {
        $instance = new GatewayDevice();
        $instance->setGatewayId($gatewayId);
        $instance->setOperator($operator);
        $instance->setManufacturer($manufacturer);
        $instance->setModel($model);
        $instance->setSdk($sdk);
        $instance->setVersionName($versionName);
        $instance->setVersion($version);
        $instance->setBattery($battery);
        $instance->setPower($power);
        $instance->setPollInterval($pollInterval);
        $instance->setUpdateDate(new \DateTime());

        return $instance;
    }
    /**
     * @return mixed
     */
    public function getGatewayId()
    {
        return $this->gatewayId;
    }

    /**
     * @param mixed $gatewayId
     */
    public function setGatewayId($gatewayId)
    {
        $this->gatewayId = $gatewayId;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param mixed $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getSdk()
    {
        return $this->sdk;
    }

    /**
     * @param mixed $sdk
     */
    public function setSdk($sdk)
    {
        $this->sdk = $sdk;
    }

    /**
     * @return mixed
     */
    public function getVersionName()
    {
        return $this->versionName;
    }

    /**
     * @param mixed $versionName
     */
    public function setVersionName($versionName)
    {
        $this->versionName = $versionName;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getBattery()
    {
        return $this->battery;
    }

    /**
     * @param mixed $battery
     */
    public function setBattery($battery)
    {
        $this->battery = $battery;
    }

    /**
     * @return mixed
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * @param mixed $power
     */
    public function setPower($power)
    {
        $this->power = $power;
    }

    /**
     * @return mixed
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param mixed $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getPollInterval()
    {
        return $this->pollInterval;
    }

    /**
     * @param mixed $pollInterval
     */
    public function setPollInterval($pollInterval)
    {
        $this->pollInterval = $pollInterval;
    }
}