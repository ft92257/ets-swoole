<?php

namespace Ets\queue;


use Ets\helper\ToolsHelper;

class BroadcastQueue extends Queue
{

    /**
     * 广播
     *
     * @param BaseJob $job
     * @param $routingKey
     * @param $hasRetryCount
     * @throws
     */
    public function broadcast(BaseJob $job, string $routingKey, int $hasRetryCount = 0)
    {
        try {
            $job->setClassName();

            // 转换为json格式存储数据
            $message = Message::build($job->toArray(), 0);

            $this->getDriver()->broadcast($this, $message, $routingKey);

        } catch (\Throwable $e) {
            // 推送失败，重试推送
            if ($hasRetryCount >= $this->pushRetryCount) {
                throw $e;
            }
            $hasRetryCount++;

            $this->broadcast($job, $routingKey, $hasRetryCount);
        }

    }


}