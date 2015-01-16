<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Mail.Exception');

/**
 * Mail abstract class
 *
 * @package Mail
 * @class Xapp_Mail
 * @error 112
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Mail
{
    /**
     * factory builder for mail wrapper classes instantiating driver class passing
     * protocol string and options
     *
     * @error 11201
     * @param string $driver expects the driver/class to load
     * @param null|string $protocol expects the protocol to use or default protocol will be used
     * @param null|mixed $options expects optional params array or object
     * @return object instant of driver class
     * @throws Xapp_Mail_Exception
     */
    public static function factory($driver, $protocol = null, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(trim((string)$driver));
        if(class_exists($class))
        {
            return new $class($protocol, $options);
        }else{
            throw new Xapp_Mail_Exception(xapp_sprintf(_("unable to create class for driver: %s"), $driver), 1120101);
        }
    }


    /**
     * singleton factory builder for mail wrapper classes instantiating driver class passing
     * protocol string and options. the singleton makes sure the class wrapper will be used always
     * with the same driver options
     *
     * @error 11202
     * @param string $driver expects the driver/class to load
     * @param null|string $protocol expects the protocol to use or default protocol will be used
     * @param null|mixed $options expects optional params array or object
     * @return object instant of driver class
     * @throws Xapp_Mail_Exception
     */
    public static function singletonFactory($driver, $protocol = null, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(trim((string)$driver));
        if(class_exists($class))
        {
            return $class::instance($protocol, $options);
        }else{
            throw new Xapp_Mail_Exception(xapp_sprintf(_("unable to create singleton instance for driver: %s"), $driver), 1120201);
        }
    }
}