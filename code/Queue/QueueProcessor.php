<?php namespace WebTorque\QueuedMailer\Queue;

use WebTorque\QueuedMailer\Transport\Transport;

class QueueProcessor
{
    /**
     * @var Transport
     */
    private $transport;

    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function process()
    {
        $batchSize = self::config()->batch_size;
        $retryTime = self::config()->retry_time;
        $appIdentifier = self::config()->application_indentifier;

        $lastAttemptTime = \DBField::create_field('SS_Datetime',
            strtotime('-' . $retryTime . 'm', strtotime(\SS_Datetime::now())));

        $batch = QueuedEmail::get()->filter(array(
            'LastAttempt:LessThanOrEqual' => $lastAttemptTime->Value()
        ))->limit($batchSize);

        foreach ($batch as $email) {
            $result = $this->transport->send(
                $appIdentifier,
                $appIdentifier . '-' . $email->ID,
                $email->To,
                $email->From,
                $email->Subject,
                $email->HTML,
                $email->Plain,
                $email->CC,
                $email->BCC,
                $email->loadAttachments(),
                $email->loadHeaders(),
                $email->ReplyTo
            );

            if ($result) {
                $email->Status = 'Sent';
                $email->Identifier = $result;
            } else {
                $email->Status = 'Failed';
            }

            $email->write();
        }
    }

    public static function config()
    {
        return \Config::inst()->forClass(__CLASS__);
    }
}