<?php

namespace Ets\event\events;

use Ets\event\BaseEvent;
use Ets\server\base\RequestInterface;

class RequestBeforeEvent extends BaseEvent
{

    /**
     * @var RequestInterface
     */
    public $request;


    public static function getName(): string
    {
        return 'RequestBeforeEvent';
    }

}