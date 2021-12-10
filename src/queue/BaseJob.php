<?php

namespace Ets\queue;

use Ets\base\Component;

abstract class BaseJob extends Component
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

    /**
     * @Override
     * @param $config
     */
    public function setAttributeByConfig($config)
    {
        foreach ($config as $field => $value) {
            $this->$field = $value;
        }
    }

    public abstract function execute();
}
