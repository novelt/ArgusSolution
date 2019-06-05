<?php
/**
 * Import Contact List
 *
 * @author fc, copied from Sites.php
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;


/**
 * Class ContactTypes
 * @package AppBundle\Entity\Import
 *
 * @JMS\XmlRoot("contact_types")
 */
class ContactTypes
{
    /**
     * @JMS\XmlList(inline = true, entry = "contact_type")
     * @JMS\Type("array<AppBundle\Entity\SesDashboardContactType>")
     */
    private $dashboardContactTypes;

    public function getDashboardContactTypes()
    {
        return $this->dashboardContactTypes;
    }

    public function setDashboardContactTypes($dashboardContactTypes)
    {
        $this->dashboardContactTypes = $dashboardContactTypes;
    }
}