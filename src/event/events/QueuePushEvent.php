<?php

namespace Ets\event;

use Ets\queue\BaseJob;

class QueuePushEvent extends BaseEvent
{

    /**
     * @var BaseJob
     */
    public $job;

    public static function getName(): string
    {
        return 'QueuePushEvent';
    }

}