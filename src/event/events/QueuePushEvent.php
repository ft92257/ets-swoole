<?php

namespace Ets\event;

class QueuePushEvent extends BaseEvent
{

    public static function getName(): string
    {
        return 'QueuePushEvent';
    }

}