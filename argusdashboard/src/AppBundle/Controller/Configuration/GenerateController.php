<?php

namespace AppBundle\Controller\Configuration;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\GenerateReportType;
use AppBundle\Form\GenerateAlertType;

class GenerateController extends BaseController
{
    const REPORT_MESSAGE_TEMPLATE = 'REPORT DISEASE=%s, YEAR=%d, WEEK=%d, ANDROIDID=%d, RID=%d';
    const ALERT_MESSAGE_TEMPLATE = 'ALERT ANDROIDID=%d';

    const ALERT_DATE_FORMAT = 'd/m/Y';

    const DISEASE_INPUT_PATTERN = '/\d+_disease_\d+/';
    const VALUE_INPUT_PATTERN = '/(value|alert)_(\d+)/';

    const ARGUS_SECRET = 'argus'; // @TODO: to be changed with ArgusConfig file value
    const GATEWAY_URI = '/argusconfig/argusGateway.php';

    public function listAction(Request $request)
    {
        $diseaseService = $this->getDiseaseService();
        $translator = $this->get('translator');

        $reportForm = $this->createForm(new GenerateReportType($this->getSupportedLocales()), null, [
            'disease_service' => $diseaseService,
            'translator' => $translator
        ]);

        $alertForm = $this->createForm(new GenerateAlertType($this->getSupportedLocales()), null, [
            'disease_service' => $diseaseService,
            'translator' => $translator
        ]);

        $reportForm->handleRequest($request);
        $alertForm->handleRequest($request);

        $viewParams = [
            'report' => [
                'form' => $reportForm->createView(),
                'titleKey' => 'Configuration.Titles.GenerateReport',
                'tab' => 'Configuration.FormItems.Generate.Report.Tab'
            ],
            'alert' => [
                'form' => $alertForm->createView(),
                'titleKey' => 'Configuration.Titles.GenerateAlert',
                'tab' => 'Configuration.FormItems.Generate.Alert.Tab'
            ],

            'actionPathKey' => 'configuration_generate',
            'active' => 'report'
        ];

        if ($reportForm->isSubmitted() && $reportForm->isValid())
        {
            $this->handleReportForm($reportForm->getData());
        }
        else if ($alertForm->isSubmitted() && $alertForm->isValid())
        {
            $this->handleAlertForm($alertForm->getData());
            $viewParams['active'] = 'alert';
        }

        return $this->render('configuration/generate/index.html.twig', $viewParams);
    }

    /**
     * Handles form concerning the alert
     * @param array $data
     * @return boolean
     */
    private function handleAlertForm($data)
    {
        $diseaseValueService = $this->getDiseaseValueService();

        $message = sprintf(self::ALERT_MESSAGE_TEMPLATE, mt_rand(0, 9999));
        foreach ($data AS $key => $value)
        {
            if (preg_match(self::VALUE_INPUT_PATTERN, $key, $match))
            {
                $diseaseValue = $diseaseValueService->getById($match[2]);
                if (!$diseaseValue) // disease value not found
                {
                    continue;
                }

                $value = ($value instanceof \DateTime) ? $value->format(self::ALERT_DATE_FORMAT) : $value; // get date as string if required
                $message .= ', ' . mb_strtoupper($diseaseValue->getKeyword()) . '=' . $value; // concat message to be sent
            }
        }

        $result = $this->runHttpRequest('+228000002', $message);

        return $result;
    }

    private function handleReportForm($data)
    {
        $diseaseService = $this->getDiseaseService();

        // generate random values for report
        $androidId = mt_rand(0, 9999);
        $rid = mt_rand(0, 9999);

        // fetch diseases values in the desired format
        $diseasesValues = $this->formatDiseasesValues($data);
        foreach($diseasesValues AS $diseaseId => $value)
        {
            $disease = $diseaseService->getById($diseaseId);
            if (!$disease) // disease not found
            {
                continue;
            }

            // week = 0 -> generate reports for whole chosen year
            if ($data['week'] == 0)
            {
                $nbWeeks = $this->getNbWeeksForYear($data['year']);
                for ($week = 1; $week <= $nbWeeks; $week++)
                {
                    $message = $this->buildReportMessage($disease->getKeyword(), $data['year'], $week, $androidId, $rid);
                    $result = $this->processRequest($data['contact']->getPhoneNumber(), $value, $message);
                }
            }
            else
            {
                $message = $this->buildReportMessage($disease->getKeyword(), $data['year'], $data['week'], $androidId, $rid);
                $result = $this->processRequest($data['contact']->getPhoneNumber(), $value, $message);
            }
        }
    }

    private function formatDiseasesValues($data)
    {
        // only fetch input values concerning diseases
        $diseasesValues = array_intersect_key($data, array_flip(preg_grep(self::DISEASE_INPUT_PATTERN, array_keys($data), 0)));

        $result = [];
        foreach ($diseasesValues AS $key => $value)
        {
            $info = explode('_', $key); // [0] = diseaseId, [1] = 'disease', [2] = diseaseValueId
            $result[$info[0]][$info[2]] = $value;
        }

        return $result;
    }

    /**
     * Build report message to be sent to Argus gateway
     * @param string $disease
     * @param int $year
     * @param int $week
     * @param int $androidId
     * @param int $rid
     * @return string
     */
    private function buildReportMessage($disease, $year, $week, $androidId, $rid)
    {
        return sprintf(
            self::REPORT_MESSAGE_TEMPLATE,
            $disease,
            $year,
            $week,
            $androidId,
            $rid
        );
    }

    // process request
    private function processRequest($phoneNumber, $value, $message)
    {
        $diseaseValueService = $this->getDiseaseValueService();

        foreach ($value AS $diseaseValueId => $val)
        {
            $diseaseValue = $diseaseValueService->getById($diseaseValueId);
            if (!$diseaseValue) // disease value not found
            {
                continue;
            }

            $message .= ', ' . mb_strtoupper($diseaseValue->getKeyword()) . '=' . $val; // concat message to be sent
        }

        $result = $this->runHttpRequest($phoneNumber, $message);
        return ($result === true ? 1 : 0);
    }

    /**
     * Get the number of weeks in the specified year
     * @param int $year
     * @return int
     */
    private function getNbWeeksForYear($year)
    {
        // current year => so return current week number
        if ($year == date('Y'))
        {
            return (int) date('W');
        }

        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format('W') === '53' ? 53 : 52);
    }

    /**
     * Build signature for HTTP request on gateway
     * @param array $params
     * @return string
     */
    private function computeSignature(array $params)
    {
        $fullUrl = $this->buildRequestUri();

        ksort($params);
        $input = $fullUrl;
        foreach ($params AS $key => $value)
        {
            $input .= ',' . $key . '=' . $value;
        }

        $input .= ',' . self::ARGUS_SECRET;

        return base64_encode(sha1($input, true));
    }

    /**
     * Build request URI for cURL, similar to Argus Config
     * @return string
     */
    private function buildRequestUri()
    {
        $is_secure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
        $protocol = $is_secure ? 'https' : 'http';

        return $protocol . '://' . $_SERVER['HTTP_HOST'] . self::GATEWAY_URI;
    }

    // send POST request to gateway with cURL
    private function runHttpRequest($phoneNumber, $message)
    {
        $params = [
            'action' => 'incoming',
            'phone_number' => $phoneNumber,
            'from' => $phoneNumber,
            'timestamp' => time(),
            'version' => 29,

            'message' => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->buildRequestUri());
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen(http_build_query($params)),
            'X-Request-Signature: ' . $this->computeSignature($params)
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}
