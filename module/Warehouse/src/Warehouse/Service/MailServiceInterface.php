<?php
/**
 * User: FlorenceColas
 * Date: 28/01/2017
 * Version: 1.00
 * MailServiceInterface: Mail service interface
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Service;

interface MailServiceInterface
{
    /**
     * Send an email
     * @param $to $subject $body $attachment
     * @return boolean
     */
    public function sendMail($to, $subject, $body, $attachment);

}

?>