<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 16/12/2016
 * Time: 11:14
 */

namespace AppBundle\Controller\WebApi;


use AppBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseRestController extends BaseController
{
    protected function getJsonResponse(array $data) {
        $jResponse = new JsonResponse();
        $jResponse->headers->set('Access-Control-Allow-Headers', 'Content-Type');
        $jResponse->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $jResponse->headers->set('Access-Control-Allow-Origin', '*');
        $jResponse->setData($data);
        return $jResponse;
    }

    protected function getMessage($code, $message=null) {
        $exceptionMessage = new Message();
        $exceptionMessage->setCode($code);
        $exceptionMessage->setMessage($message);

        return $exceptionMessage;
    }
}