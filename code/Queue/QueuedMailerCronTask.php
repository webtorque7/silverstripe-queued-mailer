<?php namespace WebTorque\QueuedMailer\Queue;


class QueuedMailerCronTask implements \CronTask
{
    public function getSchedule()
    {
        return '*/2 * * * *';
    }

    public function process()
    {
        $processor = \Injector::inst()->get('QueueProcessor');
        $processor->process();
        $processor->cleanup();
    }
}