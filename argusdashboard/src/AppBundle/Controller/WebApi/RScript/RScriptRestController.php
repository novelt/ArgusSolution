<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 04/07/2018
 * Time: 11:17
 */

namespace AppBundle\Controller\WebApi\RScript;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\WebApi\RScript\WebApiRAnalyse;
use AppBundle\Entity\WebApi\RScript\WebApiRScript;

use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * Class RScriptRestController
 *
 * @package AppBundle\Controller\WebApi\RScript
 */
class RScriptRestController extends BaseController
{
    /**
     * @return array
     * @Get("/rScripts")
     */
    public function getScriptsAction()
    {
        $result = [];

        $scripts = $this->getRScripts();
        if ($scripts === null) {
            return $result;
        }

        for ($i=0; $i < sizeof($scripts); $i++) {
            /** @var WebApiRScript $script */
            $script = new WebApiRScript();
            $script->directory = $scripts[$i]['directory'];
            $script->fileName = $scripts[$i]['file'];

            // Translate title
            $titleTranslationKey = $scripts[$i]['title'];
            $script->title = $this->getTranslator()->trans($titleTranslationKey, array(), 'angular_validation_dashboard');

            $result[] = $script;
        }

        return ['scripts' => $result];
    }

    /**
     * @return array
     * @Get("/rAnalyses")
     */
    public function getAnalysesAction()
    {
        $result = [];

        $folderAnalysesRScripts = $this->getAnalysesRScripts();

        if (! empty($folderAnalysesRScripts)) {

            $files = scandir($folderAnalysesRScripts);

            for ($i=0; $i < sizeof($files); $i++) {
                $file = $files[$i];
                $info = pathinfo($file);
                if (!is_dir($file) && ! empty($info['extension'])) {
                    $analyse = new WebApiRAnalyse();
                    $analyse->title = $info['filename'];
                    $analyse->date = date("Y-m-d H:i:s", filemtime($folderAnalysesRScripts.'/'.$file));
                    $analyse->extension = $info['extension'];
                    $analyse->size = filesize($folderAnalysesRScripts.'/'.$file);
                    $result[] = $analyse;
                }
            }
        }

        return ['analyses' => $result];
    }


    /**
     * @param $filename
     * @return Response
     * @Get("/rDownloadAnalyse/{filename}/{extension}")
     */
    public function downloadAnalyseAction($filename, $extension)
    {
        $folderAnalysesRScripts = $this->getAnalysesRScripts();

        $response = new Response();

        $filename = $folderAnalysesRScripts.'/'.$filename.'.'.$extension;

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(file_get_contents($filename));

        return $response;
    }
}