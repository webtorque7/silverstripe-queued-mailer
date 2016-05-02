<?php namespace WebTorque\QueuedMailer\Queue;

use WebTorque\QueuedMailer\Transport\Transport;

class QueueProcessor implements QueueProcessorInterface
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
        $appIdentifier = self::config()->application_identifier;

        $lastAttemptTime = \DBField::create_field('SS_Datetime',
            strtotime('-' . $retryTime . 'm', strtotime(\SS_Datetime::now())));

        $batch = QueuedEmail::get()->where(
            sprintf('("LastAttempt" <= \'%s\' OR LastAttempt IS NULL) AND "Status" <> \'Sent\'', $lastAttemptTime->getValue())
        )->limit($batchSize);

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
     * Remove old emails so we don't clutter up the db over time
     */
    public function cleanup()
    {
        $cleanupDays = self::config()->cleanup_days;
        $batchSize = self::config()->batch_size;

        $cleanupTime = \DBField::create_field('SS_Datetime',
            strtotime('-' . $cleanupDays . 'days', strtotime(\SS_Datetime::now())));

        $emails = QueuedEmail::get()->where(sprintf(
                '("Status" = \'Sent\' AND "Created" <= \'%s\') OR ("Status" = \'Failed\' AND "LastAttempt" <= \'%s\')',
                $cleanupTime->getValue(),
                $cleanupTime->getValue()
            )
        )->limit($batchSize);

        //do them individually incase they have been inherited and there are multiple tables
        //DB::query would be more efficient
        foreach ($emails as $email) {
            $email->delete();
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