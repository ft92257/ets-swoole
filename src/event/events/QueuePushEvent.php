<?php

namespace Ets\event\events;

use Ets\event\BaseEvent;
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