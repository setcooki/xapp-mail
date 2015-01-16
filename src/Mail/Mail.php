<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Mail.Interface');

/**
 * Mail Mail class
 *
 * @package Mail
 * @subpackage Mail_Mail
 * @class Xapp_Mail_Mail
 * @error 123
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Mail_Mail implements Xapp_Mail_Interface, Xapp_Singleton_Interface
{
    /**
     * option charset contains the mails charset
     *
     * @const CHARSET
     */
    const CHARSET                       = 'MAIL_MAIL_CHARSET';

    /**
     * option content type contains the mails content type
     *
     * @const CONTENT_TYPE
     */
    const CONTENT_TYPE                  = 'MAIL_MAIL_CONTENT_TYPE';

    /**
     * option headers contains optional headers
     *
     * @const HEADERS
     */
    const HEADERS                       = 'MAIL_MAIL_HEADERS';

    /**
     * option additional parameters contains optional parameters
     *
     * @const ADDITIONAL_PARAMETERS
     */
    const ADDITIONAL_PARAMETERS         = 'MAIL_MAIL_ADDITIONAL_PARAMETERS';


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CHARSET                   => XAPP_TYPE_STRING,
        self::CONTENT_TYPE              => XAPP_TYPE_STRING,
        self::HEADERS                   => XAPP_TYPE_ARRAY,
        self::ADDITIONAL_PARAMETERS     => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CHARSET                   => 1,
        self::CONTENT_TYPE              => 1,
        self::HEADERS                   => 0,
        self::ADDITIONAL_PARAMETERS     => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::CHARSET                   => 'utf-8',
        self::CONTENT_TYPE              => 'text/plain',
        self::ADDITIONAL_PARAMETERS     => ''
    );


    /**
     * contains the singleton instance
     *
     * @var null|Xapp_Mail_Mail
     */
    protected static $_instance = null;


    /**
     * class constructor set instance options
     *
     * @error 12301
     * @param null|mixed $options expects the options object
     */
    public function __construct($options = null)
    {
        xapp_init_options($options, $this);
    }


    /**
     * static singleton instance creator. see class constructor for more
     *
     * @error 12302
     * @param null|mixed $options expects the options object
     * @return null|Xapp_Mail_Mail
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }


    /**
     * dispatches/send a message composed by Xapp_Mail_Mail::compose function. will not
     * accepted anything else an throw error. returns the amount of successful mails that
     * have been sent
     *
     * @see Xapp_Mail_Mail::compose
     * @error 12303
     * @param stdClass $message expects anonymous object from compose function
     * @param array $failed array of failed receivers passed by reference
     * @return int
     * @throws Xapp_Mail_Mail_Exception
     */
    public function dispatch($message, Array &$failed = null)
    {
        $send = 0;

        if(is_object($message))
        {
            foreach((array)$message->to as $to)
            {
                if(mail($to, $message->subject, $message->message, trim(implode("\r\n", $message->headers)), xapp_get_option(self::ADDITIONAL_PARAMETERS, $this)))
                {
                    $send++;
                }else{
                    if($failed !== null)
                    {
                        $failed[] = $to;
                    }
                }
            }
            return $send;
        }else{
            throw new Xapp_Mail_Mail_Exception(_("first parameter must be instance of stdClass"), 1230301);
        }
    }


    /**
     * compose a message to be send by Xapp_Mail_Mail::dispatch function. all parameters are compiled
     * into anonymous object to be returned and send via dispatch method
     *
     * @see Xapp_Mail_Mail::dispatch
     * @error 12304
     * @param string $message expects swift message instance or message string
     * @param string $subject expects subject of message
     * @param mixed $to expects string or array of receiver(s)
     * @param null|mixed $from expects string or array of sender(s)
     * @param int $priority expects priority defaults to normal
     * @param array $headers expects optional header
     * @return XO|stdClass
     */
    public function compose($message, $subject, $to, $from = null, $priority = 3, Array $headers = array())
    {
        $_message = trim($message);
        $header   = array();
        $header[] = "MIME-Version: 1.0";
        $header[] = "Content-type: ".xapp_get_option(self::CONTENT_TYPE, $this)."; charset=" . xapp_get_option(self::CHARSET, $this);
        $header[] = "X-Mailer: PHP/".phpversion();
        if($from !== null)
        {
            $from = (array)$from;
            $header[] = "From: " . trim(array_shift($from));
        }
        if((int)$priority === 1)
        {
            $header[] = "X-Priority: 1 (Highest)";
            $header[] = "X-MSMail-Priority: High";
            $header[] = "Importance: High";
        }
        if(xapp_is_option(self::HEADERS, $this))
        {
            $header = array_merge($header, xapp_get_option(self::HEADERS, $this));
        }
        if(!empty($headers))
        {
            $header = array_merge($header, (array)$headers);
        }
        $message = new XO();
        $message->message = $_message;
        $message->subject = trim($subject);
        $message->to = $to;
        $message->from = $from;
        $message->priority = (int)$priority;
        $message->headers = $header;
        return $message;
    }
}