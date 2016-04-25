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

    /**
     * Process the email queue
     *
     * To configure add to yml file
     *
     * <code>
     * QueueProcessor:
     *   batch_size: 100
     *   retry_time: 30 #minutes to wait unit retrying
     *   application_identifier: 'MyApplication' #identifier if using multiple apps
     * </code>
     */
    public function process()
    {
        $batchSize = self::config()->batch_size;
        $retryTime = self::config()->retry_time;
        $appIdentifier = self::config()->application_indentifier;

        $lastAttemptTime = \DBField::create_field('SS_Datetime',
            strtotime('-' . $retryTime . 'm', strtotime(\SS_Datetime::now())));

        $batch = QueuedEmail::get()->where(array(
            '"LastAttempt" <= ? OR LastAttempt IS NULL' => array($lastAttemptTime->getValue())
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

    /**
     * @return \Config_ForClass
     */
    public static function config()
    {
        return \Config::inst()->forClass('QueueProcessor');
    }
}