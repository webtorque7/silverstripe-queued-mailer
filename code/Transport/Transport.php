<?php namespace WebTorque\QueuedMailer\Transport;


interface Transport
{
    /**
     * @param string $app Name of app sending email
     * @param string $identifier Unique identifier for the email
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $html
     * @param string $plain
     * @param string $cc
     * @param string $bcc
     * @param array $attachments
     * @param array $headers
     * @param string|null $replyTo
     * @return bool|array
     */
    public function send($app, $identifier, $to, $from, $subject, $html, $plain, $cc, $bcc, $attachments, $headers, $replyTo = null);
}