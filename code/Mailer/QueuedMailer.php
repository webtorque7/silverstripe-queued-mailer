<?php namespace WebTorque\QueuedMailer\Mailer;

use WebTorque\QueuedMailer\Queue\QueuedEmail;
use WebTorque\QueuedMailer\Transport\Transport;

/**
 * Created by PhpStorm.
 * User: Conrad
 * Date: 21/04/2016
 * Time: 2:05 PM
 */
class QueuedMailer extends \Mailer
{
    public function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = false, $customheaders = false)
    {
        $queuedEmail = QueuedEmail::create(array(
            'To' => $to,
            'From' => $from,
            'Subject' => $subject,
            'Plain' => $plainContent,
            'Status' => 'Queued'
        ));

        if ($attachedFiles) {
            $queuedEmail->addAttachments($attachedFiles);
        }

        if ($customheaders) {
            $queuedEmail->addHeaders($customheaders);
        }

        $queuedEmail->write();

        return true;
    }

    public function sendHTML(
        $to,
        $from,
        $subject,
        $htmlContent,
        $attachedFiles = false,
        $customheaders = false,
        $plainContent = false
    ) {
        $queuedEmail = QueuedEmail::create(array(
            'To' => $to,
            'From' => $from,
            'Subject' => $subject,
            'HTML' => $htmlContent,
            'Plain' => $plainContent,
            'Status' => 'Queued'
        ));

        if (!empty($customheaders['Cc'])) {
            $queuedEmail->CC = $customheaders['Cc'];
        }

        if (!empty($customheaders['Bcc'])) {
            $queuedEmail->BCC = $customheaders['Bcc'];
        }

        if ($attachedFiles) {
            $queuedEmail->addAttachments($attachedFiles);
        }

        if ($customheaders) {
            $queuedEmail->addHeaders($customheaders);
        }

        $queuedEmail->write();

        return true;
    }
}