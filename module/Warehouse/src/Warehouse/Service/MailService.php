<?php
/**
 * User: FlorenceColas
 * Date: 28/01/2017
 * Version: 1.00
 * MailService: Mail service
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Service;

use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Config\Config;

class MailService implements MailServiceInterface
{
    protected $smtpOptions;
    protected $from;
    protected $to;

    public function __construct() {
        //Read SMTP Options from mail.config.php
        $Config = new Config(include __DIR__ . '/../../../config/mail.config.php');
        $this->smtpOptions = $Config->get('smtpOptions')->toArray();
        $this->from = $Config->get('from');
        $this->to = $Config->get('to');
    }

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @param $attachment
     */
    public function sendMail($to, $subject, $body, $attachment)
    {
        //setup SMTP transport
        $transport = new SmtpTransport();
        $options   = new SmtpOptions($this->smtpOptions);
        $transport->setOptions($options);

        $message = new Message();
        $message->setFrom($this->from);
        if ($to == '')
            $message->addTo($this->to);
        else
            $message->addTo($to);
        $message->setSubject($subject);

        $message->setEncoding("UTF-8");
        $message->setBody($body);

        $transport->send($message);
    }
}

?>