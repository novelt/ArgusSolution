<?php

require_once(__DIR__."/../../tools/ArgusGateway/ArgusSMS.php");
require_once(__DIR__."/../globals.php");
require_once(__DIR__."/../../tools/logging_functions.php");
require_once(__DIR__."/../../tools/mysql_functions.php");
require_once(__DIR__."/../../tools/ArgusGateway/ArgusGateway_functions.php");

global $config;

$request = ArgusSMS::get_request();

header("Content-Type: {$request->get_response_type()}");

if (!$request->is_validated($config["ArgusSMS_secret"]))
{
    header("HTTP/1.1 403 Forbidden");
    LogMessage('messages', 'ArgusSMS : Invalid password');
    echo $request->render_error_response("Invalid password");

    return;
}

if (!isset($request->phone_number) || $request->phone_number == null || $request->phone_number == '' )
{
    header("HTTP/1.1 403 Forbidden");
    LogMessage('messages', 'ArgusSMS : Gateway with empty phone number');
    echo $request->render_error_response("Please specify a phone number for this device");

    return;
}

$action = $request->get_action();
$logFileName = $request->phone_number;

// Get the logs send from the gateways
try {
    $gatewayLogFileName = 'gateway_' . $logFileName;
    LogGatewayMessage($gatewayLogFileName, $request->log);
}
catch (Exception $ex) {
    LogMessage('errors',"An error occurs when trying to get logs from gateways");
}

switch ($action->type)
{
    case ArgusSMS::ACTION_INCOMING:

        LogMessage($logFileName, 'ArgusSMS : Incoming new Message ');
        $bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
        $type = strtoupper($action->message_type);
        insert_incoming_SMS($bdd, $action->from, $action->message, $action->request->phone_number);
        db_Close($bdd);

        $reply = new ArgusSMS_OutgoingMessage(); // Empty auto reply message
        $result = $request->render_response();
        echo $result;

        return;
        
    case ArgusSMS::ACTION_OUTGOING:

        LogMessage($logFileName, 'ArgusSMS : Outgoing Message(s) with phone_number : '.$action->request->phone_number);
        $messages = array();
        $bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
        // Lock Table ses_gateway_queue
        db_Lock ($bdd, __FUNCTION__,__LINE__,__FILE__, 'ses_gateway_queue', 'WRITE');
        // Get new SMS to be sent
        $messages = get_waiting_SMS($bdd, $action->request->phone_number, false);
        // If no new SMS to be sent, Get already pending SMS to retrieve sending
        if (!isset($messages) || count($messages) == 0) {
            LogMessage($logFileName, 'ArgusSMS : Outgoing Message(s) already pending ');
            $messages = get_waiting_SMS($bdd, $action->request->phone_number, true);
        }
        // Update waiting SMS to Pending SMS
        set_pending_SMS($bdd, $messages);
        $events = array();
        if ($messages)
        {
            $events[] = new ArgusSMS_Event_Send($messages);
        }
        $result = $request->render_response($events);
        LogMessage($logFileName, $result);
        // UnLock Table ses_gateway_queue
        db_UnLock($bdd,__FUNCTION__,__LINE__,__FILE__);
        db_Close($bdd);

        echo $result ;

        return;
        
    case ArgusSMS::ACTION_SEND_STATUS:

        $bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
        LogMessage($logFileName, 'ArgusSMS : Send Status ');
        $id = $action->id;
        LogMessage($logFileName, 'Message '.$id.' - status : '.$action->status );
        if ($action->status == ArgusSMS::STATUS_SENT ) {
            update_result_Sent($bdd, $id);
        } else if ($action->status == ArgusSMS::STATUS_FAILED ||
                $action->status == ArgusSMS::STATUS_CANCELLED) {
            update_result_Failed($bdd, $id);
        }
        db_Close($bdd);
        echo $request->render_response();        
        
        return;

    case ArgusSMS::ACTION_DEVICE_STATUS:

        LogMessage($logFileName, 'ArgusSMS : Device Status ');
        LogMessage($logFileName, 'ArgusSMS : Phone Number : '.$action->request->phone_number );
        LogMessage($logFileName, 'ArgusSMS : Phone Operator : '.$action->request->phone_operator );
        LogMessage($logFileName, 'ArgusSMS : Network : '.$action->request->network );
        LogMessage($logFileName, 'ArgusSMS : Now : '.$action->request->now );
        LogMessage($logFileName, 'ArgusSMS : Settings Version : '.$action->request->settings_version );
        LogMessage($logFileName, 'ArgusSMS : Battery : '.$action->request->battery );
        LogMessage($logFileName, 'ArgusSMS : Power : '.$action->request->power );
        LogMessage($logFileName, 'ArgusSMS : VersionName : '.$action->request->version_name );
        LogMessage($logFileName, 'ArgusSMS : Sdk : '.$action->request->sdk_int );
        LogMessage($logFileName, 'ArgusSMS : Manufacturer : '.$action->request->manufacturer );
        LogMessage($logFileName, 'ArgusSMS : Model : '.$action->request->model );
        LogMessage($logFileName, 'ArgusSMS : Version : '.$action->request->version );

        if (isset($action->request->phone_number) && $action->request->phone_number != '') {
            $bdd=db_Open(__FUNCTION__,__LINE__,__FILE__);
            update_gateway_device($bdd, $action->request);
            db_Close($bdd);
        } else {
            LogMessage($logFileName, 'ArgusSMS : Phone Number is null ');
        }

        echo $request->render_response();
        return;             

    case ArgusSMS::ACTION_TEST:
        LogMessage($logFileName, 'ArgusSMS : Test Connection ');
        echo $request->render_response();
        return;                             

    default:
        header("HTTP/1.1 404 Not Found");
        echo $request->render_error_response("The server does not support the requested action.");
        return;
}