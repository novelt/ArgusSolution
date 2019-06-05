<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/28/2015
 * Time: 9:43 AM
 */

namespace AppBundle\Entity;


class Constant
{
    /*
     * Full Report & Part Report possible status
     */
    const STATUS_SUPERSEDED = "SUPERSEDED";
    const STATUS_VALIDATED = "VALIDATED";
    const STATUS_REJECTED = "REJECTED";
    const STATUS_PENDING = "PENDING";
    const STATUS_CONFLICTING = "CONFLICTING";
    const STATUS_REJECTED_FROM_ABOVE = "REJECTED FROM ABOVE";
    const STATUS_ALL = "ALL";

    /*
    * Possible report period type
    */
    const PERIOD_WEEKLY = "Weekly";
    const PERIOD_MONTHLY = "Monthly";

    /*
     * Specific type of report value for ALERT
     */
    const DISEASE_ALERT = "ALERT";

    /*
     * Export/import constants
     */
    const JMS_YES = 'Yes';
    const JMS_NO = 'No';
}