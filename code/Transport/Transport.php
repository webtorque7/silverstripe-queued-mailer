<?php namespace WebTorque\QueuedMailer\Transport;


interface Transport
{

    public function send($app, $identifier, $to, $from, $subject, $html, $plain, $cc, $bcc, $attachments, $headers, $replyTo = null);
}