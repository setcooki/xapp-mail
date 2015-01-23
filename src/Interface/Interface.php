<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../core/core.php');

/**
 * Mail interface
 *
 * @package Mail
 * @author Frank Mueller <set@cooki.me>
 */
interface Xapp_Mail_Interface
{
    /**
     * dispatch a message created by compose function
     *
     * @param mixed $message expects message instance create with compose function
     * @param array $failed expects array as reference to return failed receivers
     * @return mixed
     */
    public function dispatch($message, Array &$failed = null);


    /**
     * composes a message to be returned to send by dispatch method of concrete implementation
     *
     * @param string $message expects message to send
     * @param string $subject expects subject of message
     * @param mixed $to expects string or array of receiver(s)
     * @param null|mixed $from expects string or array of sender(s)
     * @param int $priority expects priority defaults to normal
     * @param array $headers expects optional header
     * @return mixed
     */
    public function compose($message, $subject, $to, $from = null, $priority = 3, Array $headers = array());
}