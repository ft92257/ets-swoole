<?php

namespace Ets\queue\driver;

use Ets\base\EtsException;
use Ets\consts\LogCategoryConst;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Ets\pool\connector\RabbitMqConnector;
use Ets\queue\BroadcastQueue;
use Ets\queue\Loop;
use Ets\queue\Message;
use Ets\queue\Queue;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;

class QueueRabbitMqDriver extends QueueBaseDriver
{

    protected $rabbitMqComponent = RabbitMqConnector::class;

    protected $exchangeName;

    /**
     * 当前消费的消息体
     * @var  $message AMQPMessage
     */
    protected $currentMessage;

    const PROPERTY_ATTEMPT = 'attempt';

    private $errorCount = 0;

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


    public function push(Queue $queue, Message $message, int $delay = 0)
    {
        $msg = new AMQPMessage(
            ToolsHelper::toJson($message->getJobArrayData()),
            [self::PROPERTY_ATTEMPT => $message->getAttempt()]
        );

        $this->getChannel()->basic_publish($msg, $this->exchangeName, $queue->getComponentName());
    }

    /**
     * 广播模式, rabbitMq的广播模式需要设置exchange的type=fanout
     *
     * @Override
     * @param BroadcastQueue $queue
     * @param Message $message
     * @param $routingKey
     */
    public function broadcast(BroadcastQueue $queue, Message $message, $routingKey)
    {
        $msg = new AMQPMessage(
            ToolsHelper::toJson($message->getJobArrayData()),
            [self::PROPERTY_ATTEMPT => $message->getAttempt()]
        );

        $this->getChannel()->basic_publish($msg, $this->exchangeName, $routingKey);
    }


    /**
     * @param Queue $queue
     */
    public function consumeByCallback(Queue $queue)
    {
        $loop = new Loop(['maxRunningCount' => 1]);

        while (true) {
            try {
                $this->getChannel()->basic_consume(
                    $queue->getComponentName(),
                    '',
                    false,
                    false,
                    false,
                    false,
                    function (AMQPMessage $mqMessage) use ($queue, $loop) {
                        // 该处为异步执行， 设置callback
                        $this->currentMessage = $mqMessage;

                        $jobArrayData = json_decode($this->currentMessage->getBody(), true);

                        $message = Message::build($jobArrayData, $this->currentMessage->get_properties()['attempt'] ?? 0);

                        $queue->executeInCoroutine($message, $loop);
                    }
                );

                while ($this->getChannel()->is_consuming()) {
                    // 执行callback
                    $this->getChannel()->wait();
                }

            }  catch (AMQPRuntimeException $e) {

                $this->getChannel()->close();

                // 连续错误60次后结束进程
                $this->errorCount++;
                if ($this->errorCount % 60 == 0) {
                    Ets::error("队列消费异常：" . $e->getMessage() . "\n", LogCategoryConst::ERROR_QUEUE);

                    exit;
                }

                echo "rabbit异常断开，即将重试:" . $e->getMessage();

                sleep(1);
            }
        }
    }

    /**
     * 队列消费
     * @param $queue Queue
     * @return Message
     * @throws
     */
    public function consume(Queue $queue): Message
    {
        throw new EtsException("不支持该模式");
    }

    public function success(Queue $queue)
    {
        $this->currentMessage->ack();
    }

    public function retry(int $delay, int $hasRetryCount)
    {
        $this->currentMessage->set(self::PROPERTY_ATTEMPT, $hasRetryCount);

        $this->currentMessage->reject();
    }

}