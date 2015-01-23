<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../core/core.php');

xapp_import('swiftmailer/swiftmailer/lib/swift_required.php');

/**
 * Mail Swift message class
 *
 * @package Mail
 * @subpackage Mail_Swift
 * @class Xapp_Mail_Swift_Message
 * @error 114
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Mail_Swift_Message extends Swift_Message
{
    /**
     * class constructor calls parent constructor to instantiate class and pass parameters.
     * see swift implementation for further details
     *
     * @error 11401
     * @param null|string $subject expects the mail subject
     * @param null|mixed $body expects the body which can be a string or a stream
     * @param null|string $contentType expects the content type
     * @param null|string $charset expects the content type
     */
    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {
        parent::__construct($subject, $body, $contentType, $charset);
    }


    /**
     * shortcut method to construct new instance directly
     *
     * @see Xapp_Mail_Swift_Message::__construct
     * @error 11401
     * @param null|string $subject expects the mail subject
     * @param null|mixed $body expects the body which can be a string or a stream
     * @param null|string $contentType expects the content type
     * @param null|string $charset expects the content type
     * @return Xapp_Mail_Swift_Message
     */
    public static function create($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new self($subject, $body, $contentType, $charset);
    }
}