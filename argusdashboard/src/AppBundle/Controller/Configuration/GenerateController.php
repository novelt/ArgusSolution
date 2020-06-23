<?php

namespace AppBundle\Controller\Configuration;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Form\GenerateWeeklyReportType;
use AppBundle\Form\GenerateMonthlyReportType;
use AppBundle\Form\GenerateAlertType;
use AppBundle\Utils\Epidemiologic;

class GenerateController extends BaseController
{
    const REPORT_MESSAGE_TEMPLATE = '%s %s=%s, %s=%d, %s=%d, %s=%d, %s=%d';
    const ALERT_MESSAGE_TEMPLATE = '%s %s=%d';

    const ALERT_DATE_FORMAT = 'd/m/Y';
    const GLOBAL_KEYWORD = 'global_keyword_';

    const DISEASE_INPUT_PATTERN = '/\d+_disease_\d+/';
    const VALUE_INPUT_PATTERN = '/(value|alert)_(\d+)/';

    public function listAction(Request $request)
    {
        $diseaseService = $this->getDiseaseService();
        $diseaseValueService = $this->getDiseaseValueService();
        $translator = $this->getTranslator();

        $weeklyReportForm = $this->createForm(new GenerateWeeklyReportType($this->getSupportedLocales()), null, [
            'disease_value_service' => $diseaseValueService,
            'translator' => $translator
        ]);

        $monthlyReportForm = $this->createForm(new GenerateMonthlyReportType($this->getSupportedLocales()), null, [
            'disease_value_service' => $diseaseValueService,
            'translator' => $translator
        ]);

        $alertForm = $this->createForm(new GenerateAlertType($this->getSupportedLocales()), null, [
            'disease_service' => $diseaseService
        ]);

        $weeklyReportForm->handleRequest($request);
        $monthlyReportForm->handleRequest($request);
        $alertForm->handleRequest($request);

        $viewParams = [
            'report' => [
                'weekly' => [
                    'form' => $weeklyReportForm->createView(),
                    'titleKey' => 'Configuration.Titles.GenerateWeeklyReport',
                    'tab' => 'Configuration.FormItems.Generate.Report.Weekly.Tab'
                ],
                'monthly' => [
                    'form' => $monthlyReportForm->createView(),
                    'titleKey' => 'Configuration.Titles.GenerateMonthlyReport',
                    'tab' => 'Configuration.FormItems.Generate.Report.Monthly.Tab'
                ]
            ],
            'alert' => [
                'form' => $alertForm->createView(),
                'titleKey' => 'Configuration.Titles.GenerateAlert',
                'tab' => 'Configuration.FormItems.Generate.Alert.Tab'
            ],

            'actionPathKey' => 'configuration_generate',
            'active' => 'report-weekly'
        ];

        if ($weeklyReportForm->isSubmitted() && $weeklyReportForm->isValid())
        {
            $this->handleWeeklyReportForm($weeklyReportForm->getData());
        }
        else if ($monthlyReportForm->isSubmitted() && $monthlyReportForm->isValid())
        {
            $this->handleMonthlyReportForm($monthlyReportForm->getData());
            $viewParams['active'] = 'report-monthly';
        }
        else if ($alertForm->isSubmitted() && $alertForm->isValid())
        {
            $this->handleAlertForm($alertForm->getData());
            $viewParams['active'] = 'alert';
        }

        $viewParams['weeklyReportEnabled'] = $diseaseValueService->hasDiseaseValuesForPeriod('weekly') ? true : false;
        $viewParams['monthlyReportEnabled'] = $diseaseValueService->hasDiseaseValuesForPeriod('monthly') ? true : false;

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

        $message = $this->buildAlertMessage(mt_rand(0, 9999));
        foreach ($data AS $key => $value)
        {
            if (preg_match(self::VALUE_INPUT_PATTERN, $key, $match))
            {
                $diseaseValue = $diseaseValueService->getById($match[2]);
                if (!$diseaseValue) // disease value not found
                {
                    $logger = $this->get('logger');
                    $logger->error('An error occurred : Impossible to find Alert Value with technical name "' . $diseaseValue->getValue() . '" during alert generation.');
                    continue;
                }

                $value = ($value instanceof \DateTime) ? $value->format(self::ALERT_DATE_FORMAT) : $value; // get date as string if required
                $message .= ', ' . mb_strtoupper($diseaseValue->getKeyword()) . '=' . $value; // concat message to be sent
            }
        }

        $result = $this->runHttpRequest($data['contact']->getPhoneNumber(), $message);

        return $result;
    }

    private function handleWeeklyReportForm($data)
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
                $logger = $this->get('logger');
                $logger->error('An error occurred : Impossible to find Disease Value with technical name "' . $disease->getName() . '" during weekly report generation.');
                continue;
            }

            // week = 0 -> generate reports for whole chosen year
            if ($data['week'] == 0)
            {
                $nbWeeks = Epidemiologic::getNumberOfWeeksInYear($data['year'], $this->GetEpiFirstDay());
                for ($week = 1; $week <= $nbWeeks; $week++)
                {
                    $message = $this->buildReportMessage($disease->getKeyword(), $data['year'], $week, $androidId, $rid);
                    $result = $this->processRequest($data['contact']->getPhoneNumber(), $value, $message);
                }
            }
            else
            {
                $message = $this->buildReportMessage('weekly', $disease->getKeyword(), $data['year'], $data['week'], $androidId, $rid);
                $result = $this->processRequest($data['contact']->getPhoneNumber(), $value, $message);
            }
        }
    }

    private function handleMonthlyReportForm($data)
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
                $logger = $this->get('logger');
                $logger->error('An error occurred : Impossible to find Disease Value with technical name "' . $disease->getName() . '" during monthly report generation.');
                continue;
            }

            $message = $this->buildReportMessage('monthly', $disease->getKeyword(), $data['year'], $data['month'], $androidId, $rid);
            $result = $this->processRequest($data['contact']->getPhoneNumber(), $value, $message);
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
    private function buildReportMessage($period, $disease, $year, $weekOrMonth, $androidId, $rid)
    {
        if ($period == 'weekly')
        {
            $periodKeyword = 'week';
        }
        else // monthly
        {
            $periodKeyword = 'month';
        }

        $keywords = $this->getNvcRepository()->getGlobalKeyWords();

        return sprintf(
            self::REPORT_MESSAGE_TEMPLATE,
            $this->getKeywordValue($keywords, 'report'), // REPORT

            $this->getKeywordValue($keywords, 'disease'), $disease, // DISEASE
            $this->getKeywordValue($keywords, 'year'), $year, // YEAR
            $this->getKeywordValue($keywords, $periodKeyword), $weekOrMonth, // WEEK or MONTH
            $this->getParameter('argus_config')['android_keyword_id'], $androidId, // ANDROIDID
            $this->getParameter('argus_config')['report_keyword_id'], $rid // RID
        );
    }

    /**
     * Build alert message to be sent to Argus gateway
     * @param int $androidId
     * @return string
     */
    private function buildAlertMessage($androidId)
    {
        $keywords = $this->getNvcRepository()->getGlobalKeyWords();

        return sprintf(
            self::ALERT_MESSAGE_TEMPLATE,
            $this->getKeywordValue($keywords, 'alert'), // ALERT

            $this->getParameter('argus_config')['android_keyword_id'], $androidId // ANDROIDID
        );
    }

    /**
     * Get global keyword value from nvc
     * @param array $keywords
     * @param string $keyword
     * @return string
     */
    private function getKeywordValue($keywords, $keyword)
    {
        return $keywords[self::GLOBAL_KEYWORD . $keyword]['valueString'];
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

        $input .= ',' . $this->getParameter('argus_config')['gateway_password'];

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

        $gatewayUri = $this->getParameter('argus_config')['gateway_uri'];
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $gatewayUri;
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

        if (curl_errno($ch))
        {
            $errorMessage = curl_error($ch);

            $logger = $this->get('logger');
            $logger->error('An error occurred : HTTP request to gateway returned an error. Message : ' . $errorMessage);
        }

        curl_close($ch);

        return $res;
    }

    private function getNvcRepository()
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository('AppBundle:SesNvc');
    }
}
