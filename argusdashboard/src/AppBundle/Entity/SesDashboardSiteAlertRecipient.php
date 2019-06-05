<?php
/**
 * Site Alert Recipient Entity
 *
 * @author fc
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

 /**
  * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardSiteAlertRecipientRepository")
  * @ORM\Table(name="sesdashboard_sitealertrecipients", options={"collate"="utf8_general_ci"})
  *
  * @JMS\ExclusionPolicy("all")
  */
class SesDashboardSiteAlertRecipient
{
	/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

	/**
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @JMS\XmlValue
     */
    private $recipientSiteReference;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteId;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="alertRecipients")
     * @ORM\JoinColumn(name="FK_SiteId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_RecipientSiteId;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="alertRecipientsOwner")
     * @ORM\JoinColumn(name="FK_RecipientSiteId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $recipientSite;

    public function getId()
    {
        return $this->id;
    }

    public function getSiteId()
    {
        return $this->FK_SiteId;
    }

    public function setSiteId($siteId)
    {
        $this->FK_SiteId = $siteId;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite(SesDashboardSite $site)
    {
        $this->site = $site;
    }

    public function unsetSite() {
        $this->site = null;
    }

    public function getRecipientSiteId()
    {
        return $this->FK_RecipientSiteId;
    }

    public function setRecipientSiteId($recipientSiteId)
    {
        $this->FK_RecipientSiteId = $recipientSiteId;
    }

    /**
     * @return SesDashboardSite|null
     */
    public function getRecipientSite()
    {
        return $this->recipientSite;
    }

    public function setRecipientSite(SesDashboardSite $recipientSite)
    {
        $this->recipientSite = $recipientSite;

        $this->recipientSiteReference = $recipientSite->getReference();
    }

    public function unsetRecipientSite() {
        $this->recipientSite = null;

        $this->recipientSiteReference = '';
    }

    public function getRecipientSiteReference()
    {
        return $this->recipientSiteReference;
    }
}
