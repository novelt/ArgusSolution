<?php
/**
 * Import Contact List
 *
 * @author fc, copied from Sites.php
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;

/** @JMS\XmlRoot("contacts") */
class Contacts
{
    /**
     * @JMS\XmlList(inline = true, entry = "contact")
     * @JMS\Type("array<AppBundle\Entity\SesDashboardContact>")
     */
    private $dashboardContacts;

    public function getDashboardContacts()
    {
        return $this->dashboardContacts;
    }

    public function setDashboardContacts($dashboardContacts)
    {
        $this->dashboardContacts = $dashboardContacts;
    }
}