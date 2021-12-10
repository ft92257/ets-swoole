<?php

namespace Ets\queue\driver;

use Ets\Ets;
use Ets\pool\wrapper\RabbitMqWrapper;
use Ets\queue\Queue;
use PhpAmqpLib\Message\AMQPMessage;

class QueueRabbitMqDriver extends QueueBaseDriver
{

    protected $rabbitMqComponent = RabbitMqWrapper::class;

    protected $exchangeName;

    /**
     * 当前消费的消息体
     * @var  $message AMQPMessage
     */
    protected $currentMessage;


    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['rabbitMqComponent', 'exchangeName'];
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    protected function getChannel()
    {
        return Ets::component($this->rabbitMqComponent);
    }


    public function push(Queue $queue, string $message, int $delay = 0)
    {
        $msg = new AMQPMessage($message, []);

        $this->getChannel()->basic_publish($msg, $this->exchangeName, $queue->getComponentName());
    }

    /**
     * 队列消费
     * @param $queue Queue
     * @return String
     */
    public function consume(Queue $queue): string
    {
        $currentMessage = null;
        $this->getChannel()->basic_consume(
            $queue->getComponentName(),
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use (& $currentMessage) {
                // 该处为异步执行， 设置callback
                $currentMessage = $message;
            });

        while ($this->getChannel()->is_consuming()) {
            // 执行callback
            $this->getChannel()->wait();
        }

        $this->currentMessage = $currentMessage;

        return $this->currentMessage->getBody();
    }

    public function success(Queue $queue)
    {
        $this->currentMessage->ack();
    }

    public function retry(int $delay)
    {
        $this->currentMessage->reject();
    }

}