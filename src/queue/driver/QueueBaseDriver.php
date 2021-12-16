<?php

namespace Ets\queue\driver;

use Ets\base\Component;
use Ets\base\EtsException;
use Ets\queue\BroadcastQueue;
use Ets\queue\Message;

abstract class QueueBaseDriver extends Component implements QueueDriverInterface
{
    public function broadcast(BroadcastQueue $queue, Message $message, $routingKey)
    {
        throw new EtsException("不支持广播模式");
    }
}