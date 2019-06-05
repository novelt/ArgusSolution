<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 25/10/2016
 * Time: 09:42
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
class BaseEntity implements EntityInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $creationDate;

    public function __construct() {
        $this->fillCreationDate();
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
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    private function fillCreationDate()
    {
        if (!isset($this->creationDate)) {
            $this->creationDate = new \DateTime("now");
        }
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->fillCreationDate();
    }

    public function toString() {
        return sprintf('className: [%s], id:[%d], creationDate:[%s]', get_class($this), $this->id, date_format($this->creationDate, 'Y-m-d H:i:s'));
    }
}