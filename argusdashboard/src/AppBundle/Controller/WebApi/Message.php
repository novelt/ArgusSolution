<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 19-Jul-17
 * Time: 15:58
 */

namespace AppBundle\Controller\WebApi;

use JMS\Serializer\Annotation as JMS;

class Message
{
    /**
     * @var string
     * @JMS\Expose()
     * @JMS\SerializedName("code")
     */
    public $code;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\SerializedName("message")
     */
    public $message;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
}