<?php

namespace Ets\queue;

use Ets\base\Component;
use Ets\Ets;
use Ets\event\EventHelper;
use Ets\event\events\QueueErrorEvent;
use Ets\event\events\QueueFinishEvent;
use Ets\event\events\QueuePushEvent;
use Ets\queue\driver\QueueBaseDriver;
use Ets\queue\driver\QueueRedisDriver;

class Queue extends Component
{
    /**
     * @var $driver QueueBaseDriver
     */
    protected $driverComponent = QueueRedisDriver::class;

    protected $isContinue = true;

    // 推送失败重试次数
    protected $pushRetryCount = 1;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['driverComponent', 'pushRetryCount'];
    }

    /**
     * @return QueueBaseDriver
     */
    protected function getDriver()
    {
        return Ets::component($this->driverComponent);
    }

    /**
     * @param BaseJob $job
     * @param $delay int 延迟x秒执行
     * @param $hasRetryCount
     * @throws
     */
    public function push(BaseJob $job, int $delay = 0, int $hasRetryCount = 0)
    {
        try {
            $job->prepare();

            // 转换为json格式存储数据
            $message = Message::build($job->toArray(), 0);

            $this->getDriver()->push($this, $message, $delay);

            EventHelper::localTrigger(new QueuePushEvent(['job' => $job]));

        } catch (\Throwable $e) {
            // 推送失败，重试
            if ($hasRetryCount >= $this->pushRetryCount) {
                throw $e;
            }
            $hasRetryCount++;

            $this->push($job, $delay, $hasRetryCount);
        }
    }

    // 开启消费监听
    public function listen($wait = 1)
    {
        while ($this->isContinue) {
            try {
                // 间隔10毫秒消费下一个
                usleep(10000);

                $message = $this->getDriver()->consume($this);

                $job = $this->getJobByMessage($message);

                if (empty($job)) {
                    // 没有待消费的记录，等待1秒重试
                    sleep($wait);
                    continue;
                }

                go(function () use ($job, $message) {
                    $this->executeJob($job, $message->getAttempt());

                    Ets::endClear();
                });

            } catch (\Throwable $e) {
                //
                echo "队列消费异常：" . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * @param Message $message
     * @return BaseJob
     */
    protected function getJobByMessage(Message $message)
    {
        $jobArrayData = $message->getJobArrayData();
        if (empty($jobArrayData)) {
            return null;
        }

        $jobClass = $jobArrayData['className'];

        $job = new $jobClass($jobArrayData);

        return $job;
    }

    // 手动消费一条记录
    public function execute()
    {
        $message = $this->getDriver()->consume($this);
        $job = $this->getJobByMessage($message);

        if (empty($job)) {
            // 没有待消费的记录，等待3秒重试
            echo "没有待消费的记录\n";

            return;
        }

        $this->executeJob($job, null);
    }

    /**
     * @param BaseJob $job
     * @param int $hasRetryCount 第几次重试，0不重试
     */
    protected function executeJob(BaseJob $job, int $hasRetryCount)
    {
        try {
            if ($job->isExpired()) {
                return;
            }

            $job->execute();

            // 执行成功
            $this->getDriver()->success($this);

            EventHelper::localTrigger(new QueueFinishEvent([
                'job' => $job,
            ]));

        } catch (\Throwable $e) {
            // 执行失败，待重试
            $delay = $job->getNextRetryDelay($hasRetryCount);
            if ($delay > 0) {
                $hasRetryCount++;

                $this->getDriver()->retry($delay, $hasRetryCount);
            } else {
                // 结束重试，队列消费失败

                EventHelper::localTrigger(new QueueErrorEvent([
                    'job' => $job,
                    'exception' => $e,
                ]));
            }
        }
    }

}