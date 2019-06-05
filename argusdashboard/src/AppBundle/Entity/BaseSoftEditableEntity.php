<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 22-Sep-17
 * Time: 18:01
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as GEDMO; // gedmo annotations

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
abstract class BaseSoftEditableEntity extends BaseEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $originalId;

    /**
     * True if the entity is soft deleted
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isDisabled;

    /**
     * @var string
     * @Gedmo\Blameable
     * @ORM\Column(type="string", nullable=true)
     */
    private $updatedBy;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    public function __construct()
    {
        parent::__construct();
        $this->isDeleted = false;
        $this->isDisabled = false;
    }

    /**
     * @return BaseSoftEditableEntity|null
     */
    public abstract function getOriginalEntity();

    /**
     * @param BaseSoftEditableEntity|null $entity
     * @return mixed
     */
    public abstract function setOriginalEntity(BaseSoftEditableEntity $entity = null);

    /**
     * @return BaseSoftEditableEntity|null
     */
    public abstract function getEditedEntity();

    /**
     * @param BaseSoftEditableEntity|null $entity
     * @return mixed
     */
    public abstract function setEditedEntity(BaseSoftEditableEntity $entity = null);

    /**
     * @return bool
     */
    public function isOriginal() {
        return $this->getOriginalEntity() === null;
    }

    public function isSoftDeleted() {
        return $this->getIsDeleted() === true;
    }

    /**
     * @return mixed
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param mixed $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    }

    /**
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param string $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param \DateTime $updatedDate
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
    }

    /**
     * @return mixed
     */
    public function getisDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * @param mixed $isDisabled
     */
    public function setIsDisabled($isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }

    /**
     * @return int
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * @param int $originalId
     */
    public function setOriginalId($originalId)
    {
        $this->originalId = $originalId;
    }
}