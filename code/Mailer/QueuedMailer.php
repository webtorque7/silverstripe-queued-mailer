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
        $this->send(
            $to,
            $from,
            $subject,
            '',
            $attachedFiles,
            $customheaders,
            $plainContent
        );

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

        $this->send(
            $to,
            $from,
            $subject,
            $htmlContent,
            $plainContent,
            $attachedFiles,
            $customheaders
        );

        return true;
    }

    /**
     * Sends the email, if customheader['queue'] is set it adds it to a queue rather than sending immediately
     *
     * @param $to
     * @param $from
     * @param $subject
     * @param $htmlContent
     * @param $plainContent
     * @param bool $attachedFiles
     * @param bool $customheaders
     * @param bool $plainContent
     */
    public function send(
        $to,
        $from,
        $subject,
        $htmlContent,
        $plainContent = false,
        $attachedFiles = false,
        $customheaders = false
    ) {
        if ($customheaders && !empty($customheaders['queue'])) {
            unset($customheaders['queue']);
            $this->queue(
                $to,
                $from,
                $subject,
                $htmlContent,
                $plainContent,
                $attachedFiles,
                $customheaders
            );
        } else {
            /**
             * @var \WebTorque\QueuedMailer\Transport\Transport $transport
             */
            $appIdentifier = \Config::inst()->get('QueueProcessor', 'application_identifier');
            $transport = \Injector::inst()->get('MailTransport');
            $result = $transport->send(
                $appIdentifier,
                $appIdentifier . '-' . rand() . '-' . time(),
                $to,
                $from,
                $subject,
                $htmlContent,
                $plainContent,
                $customheaders && !empty($customheaders['Cc']) ? $customheaders['Cc'] : null,
                $customheaders && !empty($customheaders['Bcc']) ? $customheaders['Bcc'] : null,
                $attachedFiles,
                $customheaders
            );

            //if failed, queue it to be retried
            if (!$result) {
                $this->queue(
                    $to,
                    $from,
                    $subject,
                    $htmlContent,
                    $plainContent,
                    $attachedFiles,
                    $customheaders
                );
            }
        }
    }

    public function queue(
        $to,
        $from,
        $subject,
        $htmlContent,
        $plainContent = false,
        $attachedFiles = false,
        $customheaders = false
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
    }
}