<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 20-Jul-18
 * Time: 19:41
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardLockRepository")
 * @ORM\Table(name="sesdashboard_lock", options={"collate"="utf8_general_ci"}, uniqueConstraints={@ORM\UniqueConstraint(columns={"name"})})
 */

class SesDashboardLock implements EntityInterface
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $creationDate;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $expire;

    public function __construct() {
        $this->fillCreationDate();
    }

    private function fillCreationDate()
    {
        if (!isset($this->creationDate)) {
            $this->creationDate = new \DateTime("now");
        }
    }

    public function getId()
    {
        return $this->getName();
    }

    public function setId($id)
    {
        $this->setName($id);
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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return float
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param float $expire
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }
}