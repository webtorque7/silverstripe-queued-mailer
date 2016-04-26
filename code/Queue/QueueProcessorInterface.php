<?php
/**
 * Created by PhpStorm.
 * User: Conrad
 * Date: 26/04/2016
 * Time: 2:32 PM
 */

namespace WebTorque\QueuedMailer\Queue;


interface QueueProcessorInterface
{
    /**
     * Process mail queue
     */
    public function process();

    /**
     * Cleanup mail queue
     */
    public function cleanup();
}