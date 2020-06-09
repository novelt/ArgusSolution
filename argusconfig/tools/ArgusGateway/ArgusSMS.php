<?php

 class ArgusSMS
{
    const ACTION_INCOMING = 'incoming';
    const ACTION_FORWARD_SENT = 'forward_sent';
    const ACTION_SEND_STATUS = 'send_status';
    const ACTION_DEVICE_STATUS = 'device_status';
    const ACTION_TEST = 'test';
    const ACTION_OUTGOING = 'outgoing';
    const ACTION_AMQP_STARTED = 'amqp_started';
        // ACTION_OUTGOING should probably be const ACTION_POLL = 'poll',
        // but 'outgoing' maintains backwards compatibility between new phone versions with old servers

    const STATUS_QUEUED = 'queued';
    const STATUS_FAILED = 'failed';
    const STATUS_SENT = 'sent';
    const STATUS_CANCELLED = 'cancelled';

    const EVENT_SEND = 'send';
    const EVENT_CANCEL = 'cancel';
    const EVENT_CANCEL_ALL = 'cancel_all';
    const EVENT_LOG = 'log';
    const EVENT_SETTINGS = 'settings';

    const DEVICE_STATUS_POWER_CONNECTED = "power_connected";
    const DEVICE_STATUS_POWER_DISCONNECTED = "power_disconnected";
    const DEVICE_STATUS_BATTERY_LOW = "battery_low";
    const DEVICE_STATUS_BATTERY_OKAY = "battery_okay";
    const DEVICE_STATUS_SEND_LIMIT_EXCEEDED = "send_limit_exceeded";

    const MESSAGE_TYPE_SMS = 'sms';
    const MESSAGE_TYPE_MMS = 'mms';
    const MESSAGE_TYPE_CALL = 'call';

    // power source constants same as from Android's BatteryManager.EXTRA_PLUGGED
    const POWER_SOURCE_BATTERY = 0;
    const POWER_SOURCE_AC = 1;
    const POWER_SOURCE_USB = 2;

    static function escape($val)
    {
        return htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
    }

    private static $request;

    static function get_request()
    {
        if (!isset(self::$request))
        {
            $version = @$_POST['version'];

            if (isset($_POST['action']))
            {
                self::$request = new ArgusSMS_ActionRequest();
            }
            else
            {
                self::$request = new ArgusSMS_Request();
            }

        }
        return self::$request;
    }
}

class ArgusSMS_Request
{
    public $version;

    public $version_name;
    public $sdk_int;
    public $manufacturer;
    public $model;

    function __construct()
    {
        $this->version = (int)@$_POST['version'];

        if (preg_match('#/(?P<version_name>[\w\.\-]+) \(Android; SDK (?P<sdk_int>\d+); (?P<manufacturer>[^;]*); (?P<model>[^\)]*)\)#',
            @$_SERVER['HTTP_USER_AGENT'], $matches))
        {
            $this->version_name = $matches['version_name'];
            $this->sdk_int = $matches['sdk_int'];
            $this->manufacturer = $matches['manufacturer'];
            $this->model = $matches['model'];
        }
    }

    function supports_json()
    {
        return $this->version >= 28;
    }

    function supports_update_settings()
    {
        return $this->version >= 29;
    }

    function get_response_type()
    {
        if ($this->supports_json())
        {
            return 'application/json';
        }
        else
        {
            return 'text/xml';
        }
    }

    function render_response($events = null /* optional array of ArgusSMS_Event objects */)
    {
        if ($this->supports_json())
        {
            return json_encode(array('events' => $events));
        }
        else
        {
            ob_start();
            echo "<?xml version='1.0' encoding='UTF-8'?>\n";
            echo "<response>";

            if ($events)
            {
                foreach ($events as $event)
                {
                    echo "<messages>";
                    if ($event instanceof ArgusSMS_Event_Send)
                    {
                        if ($event->messages)
                        {
                            foreach ($event->messages as $message)
                            {
                                $type = isset($message->type) ? $message->type : ArgusSMS::MESSAGE_TYPE_SMS;
                                $id = isset($message->id) ? " id=\"".ArgusSMS::escape($message->id)."\"" : "";
                                $to = isset($message->to) ? " to=\"".ArgusSMS::escape($message->to)."\"" : "";
                                $priority = isset($message->priority) ? " priority=\"".$message->priority."\"" : "";
                                echo "<$type$id$to$priority>".ArgusSMS::escape($message->message)."</$type>";
                            }
                        }
                    }
                    echo "</messages>";
                }
            }
            echo "</response>";
            return ob_get_clean();
        }
    }

    function render_error_response($message)
    {
        if ($this->supports_json())
        {
            return json_encode(array('error' => array('message' => $message)));
        }
        else
        {
            ob_start();
            echo "<?xml version='1.0' encoding='UTF-8'?>\n";
            echo "<response>";
            echo "<error>";
            echo ArgusSMS::escape($message);
            echo "</error>";
            echo "</response>";
            return ob_get_clean();
        }
    }
}

class ArgusSMS_ActionRequest extends ArgusSMS_Request
{
    private $request_action;

    public $settings_version; // integer version of current settings (as provided by server)
    public $phone_number;   // phone number of Android phone
    public $phone_operator; // phone operator
    public $log;            // app log messages since last successful request
    public $now;            // current time (ms since Unix epoch) according to Android clock
    public $network;        // name of network, like WIFI or MOBILE (may vary depending on phone)
    public $battery;        // battery level as percentage
    public $power;          // power source integer, see ArgusSMS::POWER_SOURCE_*
    public $pollInterval;   // gateway poll interval

    function __construct()
    {
        parent::__construct();

        $this->phone_number = $_POST['phone_number'];
        $this->phone_operator = $_POST['phone_operator'];
        $this->log = $_POST['log'];
        $this->network = @$_POST['network'];
        $this->now = @$_POST['now'];
        $this->settings_version = @$_POST['settings_version'];
        $this->battery = @$_POST['battery'];
        $this->power = @$_POST['power'];
        $this->pollInterval = @$_POST['poll_interval'];
    }

    function get_action()
    {
        if (!$this->request_action)
        {
            $this->request_action = $this->_get_action();
        }
        return $this->request_action;
    }

    private function _get_action()
    {
        switch (@$_POST['action'])
        {
            case ArgusSMS::ACTION_INCOMING:
                return new ArgusSMS_Action_Incoming($this);
            case ArgusSMS::ACTION_FORWARD_SENT:
                return new ArgusSMS_Action_ForwardSent($this);
            case ArgusSMS::ACTION_OUTGOING:
                return new ArgusSMS_Action_Outgoing($this);
            case ArgusSMS::ACTION_SEND_STATUS:
                return new ArgusSMS_Action_SendStatus($this);
            case ArgusSMS::ACTION_TEST:
                return new ArgusSMS_Action_Test($this);
            case ArgusSMS::ACTION_DEVICE_STATUS:
                return new ArgusSMS_Action_DeviceStatus($this);
            case ArgusSMS::ACTION_AMQP_STARTED:
                return new ArgusSMS_Action_AmqpStarted($this);
            default:
                return new ArgusSMS_Action($this);
        }
    }

    function is_validated($correct_password)
    {
        $signature = @$_SERVER['HTTP_X_REQUEST_SIGNATURE'];
        if (!$signature)
        {
            return false;
        }

        $is_secure = (!empty($_SERVER['HTTPS']) AND filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN));
        $protocol = $is_secure ? 'https' : 'http';
        $full_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $correct_signature = $this->compute_signature($full_url, $_POST, $correct_password);

        //error_log("Correct signature: '$correct_signature'");

        return $signature === $correct_signature;
    }

    function compute_signature($url, $data, $password)
    {
        ksort($data);

        $input = $url;
        foreach($data as $key => $value)
            $input .= ",$key=$value";

        $input .= ",$password";

        return base64_encode(sha1($input, true));
    }
}

class ArgusSMS_OutgoingMessage
{
    public $id;             // ID generated by server
    public $to;             // destination phone number
    public $message;        // content of SMS message
    public $priority;       // integer priority, higher numbers will be sent first
    public $type;            // ArgusSMS::MESSAGE_TYPE_* value (default sms)
}

/*
 * An 'action' is the term for a HTTP request that app sends to the server.
 */

class ArgusSMS_Action
{
    public $type;
    public $request;

    function __construct($request)
    {
        $this->request = $request;
    }
}

class ArgusSMS_MMS_Part
{
    public $form_name;  // name of form field with MMS part content
    public $cid;        // MMS Content-ID
    public $type;       // Content type
    public $filename;   // Original filename of MMS part on sender phone
    public $tmp_name;   // Temporary file where MMS part content is stored
    public $size;       // Content length
    public $error;      // see http://www.php.net/manual/en/features.file-upload.errors.php

    function __construct($args)
    {
        $this->form_name = $args['name'];
        $this->cid = $args['cid'];
        $this->type = $args['type'];
        $this->filename = $args['filename'];

        $file = $_FILES[$this->form_name];

        $this->tmp_name = $file['tmp_name'];
        $this->size = $file['size'];
        $this->error = $file['error'];
    }
}

abstract class ArgusSMS_Action_Forward extends ArgusSMS_Action
{
    public $message;        // The message body of the SMS, or the content of the text/plain part of the MMS.
    public $message_type;   // ArgusSMS::MESSAGE_TYPE_MMS or ArgusSMS::MESSAGE_TYPE_SMS
    public $mms_parts;      // array of ArgusSMS_MMS_Part instances
    public $timestamp;      // timestamp of incoming message (added in version 12)

    function __construct($request)
    {
        parent::__construct($request);
        $this->message = @$_POST['message'];
        $this->timestamp = @$_POST['timestamp'];

        if (isset($_POST['message_type'])) {
            $this->message_type = $_POST['message_type'];
        }

        if ($this->message_type == ArgusSMS::MESSAGE_TYPE_MMS)
        {
            $this->mms_parts = array();
            foreach (json_decode($_POST['mms_parts'], true) as $mms_part)
            {
                $this->mms_parts[] = new ArgusSMS_MMS_Part($mms_part);
            }
        }
    }
}

class ArgusSMS_Action_Incoming extends ArgusSMS_Action_Forward
{
    public $from;           // Sender phone number

    function __construct($request)
    {
        parent::__construct($request);
        $this->type = ArgusSMS::ACTION_INCOMING;
        $this->from = $_POST['from'];
    }
}

class ArgusSMS_Action_ForwardSent extends ArgusSMS_Action_Forward
{
    public $to;           // Recipient phone number

    function __construct($request)
    {
        parent::__construct($request);
        $this->type = ArgusSMS::ACTION_FORWARD_SENT;
        $this->to = $_POST['to'];
    }
}

class ArgusSMS_Action_AmqpStarted extends ArgusSMS_Action
{
    public $consumer_tag;

    function __construct($request)
    {
        parent::__construct($request);
        $this->type = ArgusSMS::ACTION_AMQP_STARTED;
        $this->consumer_tag = $_POST['consumer_tag'];
    }
}

class ArgusSMS_Action_Outgoing extends ArgusSMS_Action
{
    function __construct($request)
    {
        parent::__construct($request);
        $this->type = ArgusSMS::ACTION_OUTGOING;
    }
}

class ArgusSMS_Action_Test extends ArgusSMS_Action
{
    function __construct($request)
    {
        parent::__construct($request);
        $this->type = ArgusSMS::ACTION_TEST;
    }
}

class ArgusSMS_Action_SendStatus extends ArgusSMS_Action
{
    public $status;     // ArgusSMS::STATUS_* values
    public $id;         // server ID previously used in ArgusSMS_OutgoingMessage
    public $error;      // textual description of error (if applicable)

    function __construct($request)
    {
        parent::__construct($request);
        $this->type = ArgusSMS::ACTION_SEND_STATUS;
        $this->status = $_POST['status'];
        $this->id = $_POST['id'];
        $this->error = $_POST['error'];
    }
}

class ArgusSMS_Action_DeviceStatus extends ArgusSMS_Action
{
    public $status;     // ArgusSMS::DEVICE_STATUS_* values

    function __construct($request)
    {
        parent::__construct($request);
        $this->type = ArgusSMS::ACTION_DEVICE_STATUS;

        if (isset($_POST['status'])) {
            $this->status = $_POST['status'];
        }
    }
}

/*
 * An 'event' is the term for something the server sends to the app,
 * either via a response to an 'action', or directly via AMQP.
 */

class ArgusSMS_Event
{
    public $event;

    /*
     * Formats this event as the body of an AMQP message.
     */
    function render()
    {
        return json_encode($this);
    }
}

/*
 * Instruct the phone to send one or more outgoing messages (SMS or USSD)
 */
class ArgusSMS_Event_Send extends ArgusSMS_Event
{
    public $messages;

    function __construct($messages /* array of ArgusSMS_OutgoingMessage objects */)
    {
        $this->event = ArgusSMS::EVENT_SEND;
        $this->messages = $messages;
    }
}

/*
 * Update some of the app's settings.
 */
class ArgusSMS_Event_Settings extends ArgusSMS_Event
{
    public $settings;

    function __construct($settings /* associative array of key => value pairs (values can be int, bool, or string) */)
    {
        $this->event = ArgusSMS::EVENT_SETTINGS;
        $this->settings = $settings;
    }
}

/*
 * Cancel sending a message that was previously queued in the app via a 'send' event.
 * Has no effect if the message has already been sent.
 */
class ArgusSMS_Event_Cancel extends ArgusSMS_Event
{
    public $id;

    function __construct($id /* id of previously created ArgusSMS_OutgoingMessage object (string) */)
    {
        $this->event = ArgusSMS::EVENT_CANCEL;
        $this->id = $id;
    }
}

/*
 * Cancels all outgoing messages that are currently queued in the app. Incoming mesages are not affected.
 */
class ArgusSMS_Event_CancelAll extends ArgusSMS_Event
{
    function __construct()
    {
        $this->event = ArgusSMS::EVENT_CANCEL_ALL;
    }
}

/*
 * Appends a message to the app log.
 */
class ArgusSMS_Event_Log extends ArgusSMS_Event
{
    public $message;

    function __construct($message)
    {
        $this->event = ArgusSMS::EVENT_LOG;
        $this->message = $message;
    }
}
