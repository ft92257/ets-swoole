<?php

namespace Ets\queue\driver;

use Ets\base\Component;
use Ets\base\EtsException;
use Ets\queue\BroadcastQueue;

abstract class QueueBaseDriver extends Component implements QueueDriverInterface
{
    public function broadcast(BroadcastQueue $queue, string $message, $routingKey)
    {
        throw new EtsException("redis队列不支持广播模式");
    }
}