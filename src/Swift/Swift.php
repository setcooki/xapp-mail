<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../core/core.php');

xapp_import('swiftmailer/swiftmailer/lib/swift_required.php');
xapp_import('xapp.Mail.Interface');
xapp_import('xapp.Mail.Swift.Exception');

/**
 * Mail Swift class
 *
 * @package Mail
 * @subpackage Mail_Swift
 * @class Xapp_Mail_Swift
 * @error 113
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Mail_Swift extends Swift_Mailer implements Xapp_Mail_Interface, Xapp_Singleton_Interface
{
    /**
     * constant to define mail protocol
     *
     * @const PROTOCOL_MAIL
     */
    const PROTOCOL_MAIL         = 'mail';

    /**
     * constant to define smtp protocol
     *
     * @const PROTOCOL_SMTP
     */
    const PROTOCOL_SMTP         = 'smtp';

    /**
     * constant to define sendmail protocol
     *
     * @const PROTOCOL_SENDMAIL
     */
    const PROTOCOL_SENDMAIL     = 'sendmail';

    /**
     * constant to define postfix protocol
     *
     * @const PROTOCOL_POSTFIX
     */
    const PROTOCOL_POSTFIX      = 'postfix';

    /**
     * transport option can contain instance of swift transport class
     * or params to load transport class required for protocol passed
     * in constructor
     *
     * @const TRANSPORT
     */
    const TRANSPORT             = 'MAIL_SWIFT_TRANSPORT';

    /**
     * charset option to be used as default value
     *
     * @const CHARSET
     */
    const CHARSET               = 'MAIL_SWIFT_CHARSET';

    /**
     * host option containing server host string to be used for protocols
     * smtp and postfix
     *
     * @const HOST
     */
    const HOST                  = 'MAIL_SWIFT_HOST';

    /**
     * port option containing mail server port to be used for protocols
     * smtp and postfix
     *
     * @const PORT
     */
    const PORT                  = 'MAIL_SWIFT_PORT';

    /**
     * timeout option containing timeout value to be used for protocols
     * smtp and postfix
     *
     * @const TIMEOUT
     */
    const TIMEOUT               = 'MAIL_SWIFT_TIMEOUT';

    /**
     * encryption option containing encryption string required by swift
     * to send secure email for protocols smtp and postfix
     *
     * @const ENCRYPTION
     */
    const ENCRYPTION            = 'MAIL_SWIFT_ENCRYPTION';

    /**
     * default email recipient option to set default recipients when no
     * recipients will be passed via send/compose method
     *
     * @const DEFAULT_RECIPIENTS
     */
    const DEFAULT_RECIPIENTS    = 'MAIL_SWIFT_RECIPIENTS';


    /**
     * contains the swift transport instance
     *
     * @var null|object
     */
    protected $_transport = null;

    /**
     * contains the protocol string
     *
     * @var null|string
     */
    protected $_protocol = null;

    /**
     * contains the singleton instance
     *
     * @var null|Xapp_Mail_Swift
     */
    protected static $_instance = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::TRANSPORT             => array(XAPP_TYPE_STRING, XAPP_TYPE_ARRAY, XAPP_TYPE_OBJECT, XAPP_TYPE_CLASS),
        self::CHARSET               => XAPP_TYPE_STRING,
        self::HOST                  => XAPP_TYPE_STRING,
        self::PORT                  => XAPP_TYPE_INT,
        self::TIMEOUT               => XAPP_TYPE_INT,
        self::ENCRYPTION            => XAPP_TYPE_STRING,
        self::DEFAULT_RECIPIENTS    => array(XAPP_TYPE_STRING, XAPP_TYPE_ARRAY)
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::TRANSPORT             => 1,
        self::CHARSET               => 0,
        self::HOST                  => 0,
        self::PORT                  => 0,
        self::TIMEOUT               => 0,
        self::ENCRYPTION            => 0,
        self::DEFAULT_RECIPIENTS    => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::TRANSPORT         => array(),
        self::HOST              => 'localhost',
        self::PORT              => 25,
        self::TIMEOUT           => 30,
        self::ENCRYPTION        => null
    );


    /**
     * class constructor inits options calls parent constructor and setups
     * instance
     *
     * @error 11301
     * @param string $protocol expects optional protocol string or is set by default
     * @param null|mixed $options expects the options object
     */
    public function __construct($protocol = 'mail', $options = null)
    {
        $this->_protocol = strtolower(trim((string)$protocol));
        xapp_init_options($options, $this);
        if(!xapp_is_option(self::CHARSET, $this))
        {
            xapp_set_option(self::CHARSET, strtoupper(xapp_conf(XAPP_CONF_CHARSET)), $this);
        }
        $this->init();
        parent::__construct($this->_transport);
        $this->setup();
    }


    /**
     * setup function currently set the charset only
     *
     * @error 11302
     * @return void
     */
    protected function setup()
    {
        Swift_Preferences::getInstance()->setCharset(xapp_get_option(self::CHARSET, $this));
    }


    /**
     * init function will instantiate swift transport class according to protocol passed in
     * constructor and will valid passed transport options. NOTE: see swift transport classes
     * for required parameter and pass them according to this methods required parameter. if
     * you use "smtp" or "postfix" you can pass the parameters: "host", "port", "encryption",
     * "timeout", "username", "password" or default params will be used which can be set
     * as class instance options
     *
     * @error 11303
     * @return void
     * @throws Xapp_Mail_Swift_Exception
     */
    protected function init()
    {
        try
        {
            if(!(xapp_get_option(self::TRANSPORT) instanceof Swift_Transport))
            {
                $params = xapp_get_option(self::TRANSPORT, $this);
                $protocol = strtolower($this->_protocol);
                switch($protocol)
                {
                    case (in_array($protocol, array(self::PROTOCOL_MAIL, self::PROTOCOL_SENDMAIL))):
                        if(is_array($params))
                        {
                            $params = (string)array_shift($params);
                        }else{
                            $params = (string)$params;
                        }
                        $this->_transport = Swift_MailTransport::newInstance($params);
                        break;
                    case (in_array($protocol, array(self::PROTOCOL_SMTP, self::PROTOCOL_POSTFIX))):
                        $params = (array)$params;
                        if(!isset($params['host']))
                        {
                            $params['host'] = xapp_get_option(self::HOST, $this);
                        }
                        if(!isset($params['port']))
                        {
                            $params['port'] = xapp_get_option(self::PORT, $this);
                        }
                        if(isset($params['encryption']))
                        {
                            $params['encryption'] = (string)$params['encryption'];
                        }else{
                            $params['encryption'] = xapp_get_option(self::ENCRYPTION, $this);
                        }
                        $this->_transport = Swift_SmtpTransport::newInstance((string)$params['host'], (int)$params['port'], $params['encryption']);
                        if(isset($params['timeout']))
                        {
                            $this->_transport->setTimeout((int)$params['timeout']);
                        }else{
                            $this->_transport->setTimeout((int)xapp_get_option(self::TIMEOUT, $this));
                        }
                        if(isset($params['username']))
                        {
                            $this->_transport->setUsername((string)$params['username']);
                        }
                        if(isset($params['password']))
                        {
                            $this->_transport->setPassword((string)$params['password']);
                        }
                        break;
                    default:
                        throw new Xapp_Mail_Swift_Exception(xapp_sprintf(__("mail protocol: %s is not supported by swift mailer"), $protocol), 1130302);
                }
            }else{
                $this->_transport = xapp_get_option(self::TRANSPORT, $this);
            }
        }
        catch(Swift_SwiftException $e)
        {
            throw new Xapp_Mail_Swift_Exception(xapp_sprintf(__("swift mail error: %d, %s"), $e->getCode(), $e->getMessage()), 1130301);
        }
    }


    /**
     * static singleton instance creator. see class constructor for more
     *
     * @error 11304
     * @see Xapp_Mail_swift::__construct
     * @param string $protocol expects optional protocol string or is set by default
     * @param null|mixed $options expects the options object
     * @return null|Xapp_Mail_Swift
     */
    public static function instance($protocol = 'mail', $options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($protocol, $options);
        }
        return self::$_instance;
    }


    /**
     * dispatch/send a message which has been created as Swift_Mime_Message instance will
     * invoke swift parent send() method to send email(s). will throw error if message is
     * not an instance of Swift_Mime_Message. returns the number of successful mails sent
     *
     * @see Xapp_Mail_Swift::compose
     * @error 11305
     * @param Swift_Message $message expects instance of Swift_Mime_Message
     * @param array $failed array of failed receivers passed by reference
     * @return int
     * @throws Xapp_Mail_Swift_Exception
     */
    public function dispatch($message, Array &$failed = null)
    {
        try
        {
            if($message instanceof Swift_Message)
            {
                return parent::send($message, $failed);
            }else{
                throw new Xapp_Mail_Swift_Exception(__("first parameter must be instance of Swift_Message"), 1130502);
            }
        }
        catch(Swift_SwiftException $e)
        {
            throw new Xapp_Mail_Swift_Exception(xapp_sprintf(__("swift mail error: %d, %s"), $e->getCode(), $e->getMessage()), 1130501);
        }
    }


    /**
     * quick compose function will compose a message/mail but not send it straight away. the composed
     * message can be send using Xapp_Mail_Swift::send or via Xapp_Mail_Swift::dispatch
     *
     * @error 11306
     * @param string $message expects swift message instance or message string
     * @param string $subject expects subject of message
     * @param mixed $to expects string or array of receiver(s)
     * @param null|mixed $from expects string or array of sender(s)
     * @param int $priority expects priority defaults to normal
     * @param array $headers expects optional header
     * @return Xapp_Mail_Swift_Message
     * @throws Xapp_Mail_Swift_Exception
     */
    public function compose($message, $subject, $to, $from = null, $priority = 3, Array $headers = array())
    {
        try
        {
            if($to === null && xapp_is_option(self::DEFAULT_RECIPIENTS, $this))
            {
                $to = xapp_get_option(self::DEFAULT_RECIPIENTS, $this);
            }
            return Xapp_Mail_Swift_Message::create()
                ->setFrom($from)
                ->setTo($to)
                ->setSubject((string)$subject)
                ->setBody($message)
                ->setPriority((int)$priority);
        }
        catch(Swift_SwiftException $e)
        {
            throw new Xapp_Mail_Swift_Exception(xapp_sprintf(__("swift mail error: %d, %s"), $e->getCode(), $e->getMessage()), 1130601);
        }
    }
}