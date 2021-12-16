<?php

namespace Ets\event;

use Ets\queue\BaseJob;

class QueueErrorEvent extends BaseEvent
{

    /**
     * @var BaseJob
     */
    public $job;

    /**
     * @var \Throwable
     */
    public $exception;

    public static function getName(): string
    {
        return 'QueueErrorEvent';
    }

}