<?php

namespace Ets\queue;

use Ets\base\Component;
use Ets\Ets;
use Ets\event\EventHelper;
use Ets\event\QueuePushEvent;
use Ets\helper\ToolsHelper;
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
     */
    public function push(BaseJob $job, int $delay = 0)
    {
        try {
            $job->setClassName();

            // 转换为json格式存储数据
            $message = ToolsHelper::toJson($job);

            $this->getDriver()->push($this, $message, $delay);

            EventHelper::localTrigger(QueuePushEvent::build($job));

        } catch (\Throwable $e) {
            // 推送失败，重试 todo
        }
    }

    // 开启消费监听
    public function listen()
    {
        while ($this->isContinue) {
            try {
                // 间隔10毫秒消费下一个
                usleep(10000);

                $message = $this->getDriver()->consume($this);

                $job = $this->getJobByMessage($message);

                if (empty($job)) {
                    // 没有待消费的记录，等待3秒重试
                    sleep(3);
                    continue;
                }

                $this->executeJob($job);

            } catch (\Throwable $e) {
                //
                echo "队列消费异常：" . $e->getMessage() . "\n";
            }
        }
    }

    protected function getJobByMessage($message)
    {
        $params = json_decode($message, true);
        $jobClass = $params['className'];

        $job = new $jobClass($params);

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

        $this->executeJob($job);
    }

    protected function executeJob(BaseJob $job)
    {
        try {
            $job->execute();

            // 执行成功 todo
            $this->getDriver()->success($this);

        } catch (\Throwable $e) {
            // 执行失败，待重试 todo

        }
    }

}