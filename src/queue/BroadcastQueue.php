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
     */
    public function broadcast(BaseJob $job, $routingKey)
    {
        try {
            $job->setClassName();

            // 转换为json格式存储数据
            $message = ToolsHelper::toJson($job);

            $this->getDriver()->broadcast($this, $message, $routingKey);

        } catch (\Throwable $e) {
            // 推送失败，重试 todo
        }

    }


}