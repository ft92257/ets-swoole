<?php

namespace Ets\queue\driver;

use Ets\queue\Queue;

interface QueueDriverInterface
{

    /**
     * 推送队列数据
     * @param $queue Queue
     * @param $message
     * @param $delay int 延迟x秒
     */
    public function push(Queue $queue, string $message, int $delay = 0);

    /**
     * 队列消费
     * @param $queue Queue
     * @return String 消息内容
     */
    public function consume(Queue $queue): string;

    /**
     * 消费成功处理
     * @param Queue $queue
     * @return mixed
     */
    public function success(Queue $queue);

    /**
     * 失败重试
     * @param int $delay
     * @return mixed
     */
    public function retry(int $delay);

}