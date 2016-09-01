<?php namespace WebTorque\QueuedMailer\Transport;


class SendinBlueTransport implements Transport
{
    /**
     * @var \Sendinblue\Mailin
     */
    private $mailin;

    /**
     * IP Address of SendinBlue server
     * @var string
     */
    private $ipAddress;

    public function __construct($url, $accessKey, $ipAddress = null)
    {
        $this->mailin = new \Sendinblue\Mailin($url, $accessKey);
        $this->ipAddress = $ipAddress;
    }

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
    public function send($app, $identifier, $to, $from, $subject, $html, $plain, $cc, $bcc, $attachments, $headers, $replyTo = null)
    {
        if (empty($headers)) $headers = array();

        //add some extra info for tracking etc
        $headers['X-Mailin-custom'] = $identifier;
        $headers['X-Mailin-Tag'] = $app;

        if (!empty($this->ipAddress)) {
            $headers['X-Mailin-IP'] = $this->ipAddress;
        }

        $toAddress = $this->extractEmailToDetails($to);

        $data = array(
            'to' => array($toAddress['email'] => $toAddress['name']),
            'from' => array($from),
            'subject' => $subject,
            'html' => !empty($html) ? $html : $plain,
            'headers' => $headers
        );

        if (!empty($attachments)) {
            $data['attachment'] = $attachments;
        }

        if (!empty($replyTo)) {
            $data['replyTo'] = $replyTo;
        }

        if (!empty($cc)) {
            $ccs = explode(',', $cc);
            foreach ($ccs as $aCc) {
                $ccDetails = $this->extractEmailToDetails($aCc);
                $data['ccc'][$ccDetails['email']] = $ccDetails['name'];
            }
        }

        if (!empty($bcc)) {
            $bccs = explode(',', $bcc);
            foreach ($bccs as $aBcc) {
                $bccDetails = $this->extractEmailToDetails($aBcc);
                $data['bcc'][$bccDetails['email']] = $bccDetails['name'];
            }
        }

        $result = $this->mailin->send_email($data);

        return $result['code'] === 'success' ? $result['data']['message-id'] : false;
    }

    /**
     * Returns array in the format:
     * <code>
     * array(
     *     'name' => 'John Smith',
     *     'email' => 'john.smith@email.com'
     * );
     * </code>
     *
     * @param $to
     * @return array
     */
    protected function extractEmailToDetails($to)
    {
        $email = $to;
        $name = '';

        if (stripos($to, '<') !== false) {

            $parts = explode('<', $to);
            $name = $parts[0];

            preg_match('/\\<(.*?)\\>/', $to, $matches);

            if (!empty($matches)) {
                $email = $matches[1];
            }
        }

        return array(
            'name' => $name,
            'email' => $email
        );
    }
}