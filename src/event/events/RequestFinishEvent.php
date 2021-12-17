<?php

namespace Ets\event\events;

use Ets\event\BaseEvent;
use Ets\server\base\RequestInterface;
use Ets\server\base\ResponseInterface;

class RequestFinishEvent extends BaseEvent
{

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var ResponseInterface
     */
    public $response;

    public static function getName(): string
    {
        return 'RequestFinishEvent';
    }

}