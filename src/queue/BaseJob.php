<?php

namespace Ets\queue;

use Ets\base\BaseArrayObject;

abstract class BaseJob extends BaseArrayObject
{
    // 是否记录队列执行日志
    protected $isLog = true;

    // 有效期
    protected $jobExpireTime;

    // job有效时间(秒)
    protected $jobExpireSeconds = 86400;

    // 重试配置（秒）
    protected $jobRetryConfig = [60, 180];

    // 已重试次数
    protected $retryCount;

    protected $className;

    public function setClassName()
    {
        $this->className = get_class($this);
    }

    public abstract function execute();
}
