<?php

namespace Ets\event\events;

use Ets\event\BaseEvent;
use Ets\server\base\RequestInterface;
use Ets\server\base\ResponseInterface;

class RequestErrorEvent extends BaseEvent
{
    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @var \Throwable
     */
    public $exception;

    public static function getName(): string
    {
        return 'RequestErrorEvent';
    }

}