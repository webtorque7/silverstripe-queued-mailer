<?php namespace WebTorque\QueuedMailer\Transport;


class SendinBlueTransport implements Transport
{
    /**
     * @var \SendinBlue\Mailin
     */
    private $mailin;

    public function __construct($url, $accessKey)
    {
        $this->mailin = new \SendinBlue\Mailin($url, $accessKey);
    }

    public function send($app, $identifier, $to, $from, $subject, $html, $plain, $cc, $bcc, $attachments, $headers, $replyTo = null)
    {
        if (empty($headers)) $headers = array();

        //add some extra info for tracking etc
        $headers['X-Mailin-custom'] = $identifier;
        $headers['X-Mailin-Tag'] = $app;

        $data = array(
            'to' => $to,
            'from' => $from,
            'subject' => $subject,
            'html' => !empty($html) ? $html : $plain,
            'headers' => $headers
        );

        if ($attachments) {
            $data['attachment'] = $attachments;
        }

        if ($replyTo) {
            $data['replyTo'] = $replyTo;
        }
;
        $result = $this->mailin->send_email($data);

        return $result['code'] === 'success' ? $result['data']['message-id'] : false;
    }
}