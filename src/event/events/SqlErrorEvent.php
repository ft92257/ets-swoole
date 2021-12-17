<?php

namespace Ets\event\events;

use Ets\event\BaseEvent;

class SqlErrorEvent extends BaseEvent
{

    /**
     * @var string
     */
    public $sql;

    /**
     * @var string
     */
    public $error;

    public static function getName(): string
    {
        return 'SqlErrorEvent';
    }

}