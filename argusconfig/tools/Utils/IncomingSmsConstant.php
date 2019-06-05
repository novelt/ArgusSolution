<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 25/10/2017
 * Time: 16:54
 */
abstract class IncomingSmsStatus
{
    const STATUS_NEW = 0;                                   // Just arrived
    const STATUS_NOT_PROCESSED = 1;                         // Not Processed
    const STATUS_PROCESSED = 2;                             // Processed
    const STATUS_IGNORED = 3;                               // Ignored
    const STATUS_PHONE_NUMBER_UNKNOWN = 4;                  // Unknown contact number
    const STATUS_PHONE_NUMBER_GATEWAY = 5;                  // Number corresponding to a gateway number

    const STATUS_ERROR = 10;                                // Error
}

abstract class IncomingSmsType
{
    const TYPE_UNKNOWN = 0 ;                                // Unknown Message
    const TYPE_ARGUS = 1 ;                                  // ARGUS Message
    const TYPE_CONFIG = 2 ;                                 // CONFIG Message
    const TYPE_OTHER = 3 ;                                  // Other messages : - AVADAR ? ARGUS Case ?
}