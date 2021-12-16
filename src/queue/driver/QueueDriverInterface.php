<?php

namespace Ets\queue\driver;

use Ets\queue\Message;
use Ets\queue\Queue;

interface QueueDriverInterface
{

    /**
     * 推送队列数据
     * @param $queue Queue
     * @param $message
     * @param $delay int 延迟x秒
     */
    public function push(Queue $queue, Message $message, int $delay = 0);

    /**
     * 队列消费
     * @param $queue Queue
     * @return Message 消息内容
     */
    public function consume(Queue $queue): Message;

    /**
     * 消费成功处理
     * @param Queue $queue
     * @return mixed
     */
    public function success(Queue $queue);

    /**
     * 失败重试
     * @param int $delay
     * @param int $hasRetryCount
     * @return mixed
     */
    public function retry(int $delay, int $hasRetryCount);

}