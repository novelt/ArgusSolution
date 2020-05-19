<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 10/1/2015
 * Time: 2:59 PM
 */

namespace AppBundle\Controller;

use AppBundle\Utils\Epidemiologic;
use AppBundle\Utils\Helper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
* Controller used to manage import configuration
*
* @Route("/config")
*
*/
class ConfigController extends Controller
{
    ///**
    //  * @Route("/", name="config_index")
    //  */
    public function indexAction()
    {
        $importService = $this->container->get('ImportService');

        // directory of sql reports templates
        $directorySqlTemplates = $this->get('kernel')->getRootDir()  . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'reports' ;
        // directory of Dashboard reports templates
        $directoryDashboardTemplates = $directorySqlTemplates . DIRECTORY_SEPARATOR . 'Dashboard' ;

        //directory of dashboardReports application sql files
        $pathReports = $this->container->getParameter('path_reports') . DIRECTORY_SEPARATOR ;
        //directory of dashboard Reports applications Dashboard files
        $pathDashboardsReports = $this->container->getParameter('path_reports_dashboard') . DIRECTORY_SEPARATOR ;

        return $this->render('config/index.html.twig', array('Reports' => $importService->getReportsTranslated($directorySqlTemplates, $pathReports, $directoryDashboardTemplates, $pathDashboardsReports)));
    }

    /*
     * Translate Report files into locale
     */
    public function reportTranslateAction()
    {
        // Translate Sql reports
        $directorySqlTemplates = $this->get('kernel')->getRootDir()  . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'reports' ;
        $pathReports = $this->container->getParameter('path_reports') . DIRECTORY_SEPARATOR ;

        $files = glob($directorySqlTemplates."/*.twig", GLOB_BRACE);
        {
            foreach($files as $file) {
                $fileName = basename($file);
                $fileNameOutput = basename($file, ".twig");
                $fileContent = $this->render('reports/' . $fileName);

                // Init week calculation variables regarding epi_first_day
                $content = $fileContent->getContent();

                file_put_contents($pathReports . $fileNameOutput, $content);
            }
        }

        // Translate Php Dashboard reports
        $directoryPhpTemplates = $directorySqlTemplates . DIRECTORY_SEPARATOR . 'Dashboard';
        $pathReportsPhp = $this->container->getParameter('path_reports_dashboard') . DIRECTORY_SEPARATOR ;

        $files = glob($directoryPhpTemplates."/*.{php.twig}", GLOB_BRACE);
        {
            foreach($files as $file){
                $fileName = basename($file);
                $fileNameOutput = basename($file, ".twig");
                $fileContent = $this->render('reports/Dashboard/' . $fileName);
                $content = $fileContent->getContent();
                file_put_contents($pathReportsPhp . $fileNameOutput, $content);
            }
        }

        return $this->indexAction();
    }
}
