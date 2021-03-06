<?php

namespace Ets\queue\driver;

use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Ets\pool\connector\RedisConnector;
use Ets\queue\Message;
use Ets\queue\Queue;

class QueueRedisDriver extends QueueBaseDriver
{
    protected $redisComponent = RedisConnector::class;

    // 分片数量，使数据尽量均匀分撒到redis集群上
    protected $shardCount = 8;

    protected $pushChannelIndex;

    protected $consumeChannelIndex;

    // 当前消费的消息id
    protected $currentMessageId;

    protected $currentChannel;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['redisComponent', 'shardCount'];
    }

    protected function getRedis()
    {
        return Ets::component($this->redisComponent);
    }

    protected function getChannel($queueName, $index)
    {
        return $queueName . $index;
    }

    /**
     * 推送队列数据
     * @param $queue Queue
     * @param $message
     * @param $delay int 延迟执行时间
     * @throws
     */
    public function push(Queue $queue, Message $message, int $delay = 0)
    {
        $redis = $this->getRedis();
        $index = $this->plusPushChannelIndex();
        $channel = $this->getChannel($queue->getComponentName(), $index);

        $id = $redis->incr("$channel.message_id");

        $redis->multi();

        $redis->hset("$channel.messages", $id, ToolsHelper::toJson($message->getJobArrayData()));

        $redis->hset("$channel.attempts", $id, $message->getAttempt());

        if (! $delay) {
            $redis->lpush("$channel.waiting", $id);
        } else {
            $redis->zadd("$channel.delayed", time() + $delay, $id);
        }

        $redis->exec();

        if (! $redis->hexists("$channel.attempts", $id)) {
            throw new EtsException("队列数据推送失败");
        }

        return $id;
    }

    /**
     * Deletes message by ID
     *
     * @param $channel
     * @param int $id of a message
     */
    protected function delete($channel, $id)
    {
        $this->getRedis()->hdel("$channel.messages", $id);
        $this->getRedis()->hdel("$channel.attempts", $id);
    }

    /**
     * @param $channel
     * @return array|null payload
     */
    protected function reserve($channel)
    {
        // Moves delayed jobs into waiting list with lock for one second
        if ($this->getRedis()->set("$channel.moving_lock", true, ['NX', 'EX' => 1])) {
            // 移动延迟到时间的messageId到立即消费队列
            $this->moveExpired($channel, "$channel.delayed");
        }

        // Find a new waiting message
        $id = $this->getRedis()->rpop("$channel.waiting");
        if (! $id) {
            return null;
        }

        $this->currentMessageId = $id;

        $message = $this->getRedis()->hget("$channel.messages", $id);
        $attempt = $this->getRedis()->hget("$channel.attempts", $id);

        return [$attempt, $message];
    }

    /**
     * @param $channel
     * @param string $from
     */
    protected function moveExpired($channel, $from)
    {
        $now = time();
        if ($expired = $this->getRedis()->zrevrangebyscore($from, $now, '-inf')) {
            $this->getRedis()->zremrangebyscore($from, '-inf', $now);
            foreach ($expired as $id) {
                $this->getRedis()->rpush("$channel.waiting", $id);
            }
        }
    }

    protected function plusPushChannelIndex()
    {
        if ($this->shardCount <= 1) {
            return '';
        }

        $this->pushChannelIndex++;

        if ($this->pushChannelIndex > $this->shardCount) {
            $this->pushChannelIndex = 1;
        }

        return '-' . $this->pushChannelIndex;
    }

    protected function plusConsumeChannelIndex()
    {
        if ($this->shardCount <= 1) {
            return '';
        }

        $this->consumeChannelIndex++;

        if ($this->consumeChannelIndex > $this->shardCount) {
            $this->consumeChannelIndex = 1;
        }

        return '-' . $this->consumeChannelIndex;
    }

    /**
     * 队列消费
     * @param $queue Queue
     * @return Message
     */
    public function consume(Queue $queue): Message
    {
        $index = $this->plusConsumeChannelIndex();
        $channel = $this->getChannel($queue->getComponentName(), $index);
        $this->currentChannel = $channel;

        $messages = $this->reserve($channel);
        if (empty($messages)) {
            return new Message();
        }

        $jobArrayData = json_decode($messages[1], true);
        if (empty($jobArrayData)) {
            return new Message();
        }

        return Message::build($jobArrayData, $messages[0]);
    }

    public function success(Queue $queue)
    {
        if ($this->currentMessageId) {
            // 清除消息内容
            $this->delete($this->currentChannel, $this->currentMessageId);
        }
    }

    public function retry(int $delay, int $hasRetryCount)
    {
        $channel = $this->currentChannel;

        $this->getRedis()->hset("$channel.attempts", $this->currentMessageId, $hasRetryCount);

        $this->getRedis()->zadd("$channel.delayed", time() + $delay, $this->currentMessageId);
    }

}