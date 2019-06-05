<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 03/08/2016
 * Time: 13:44
 */

namespace AppBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Controller used to customize Argus
 *
 * @Route("/custom")
 *
 */
class CustomController extends BaseController
{

    /**
     * @Route("/", name="custom_applicationName")
     */
    public function applicationNameAction(Request $request)
    {
        // Get File ApplicationName.txt
        $appName = self::getApplicationName();
        return $this->render('custom/applicationName.html.twig', array('Name' => $appName));
    }

    /**
     * @Route("/", name="custom_applicationLogo")
     */
    public function applicationLogoAction(Request $request)
    {
        // If the logo.png file exists
        $logo = self::logoExist();
        return $this->render('custom/applicationLogo.html.twig', array('Logo' => $logo));
    }

    private function logoExist()
    {
        $fileName = self::getCustomDir()."logo.png";

        if (file_exists($fileName)){
            return true;
        }

        return false ;
    }

    private function getApplicationName()
    {
        $appName = '';

        $fileName = self::getCustomDir()."applicationName.txt";

        if (file_exists($fileName))
        {
            $file = fopen($fileName, 'r');
            $appName = fread($file, filesize($fileName));
            fclose($file);
        }

        return $appName;
    }

    private function getCustomDir()
    {
        return getcwd(). '/custom/' ;
    }
}