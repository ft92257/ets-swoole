<?php

namespace Ets\queue;

use Ets\base\BaseArrayObject;

abstract class BaseJob extends BaseArrayObject
{
    protected $jobSn;

    // 是否记录队列执行日志
    protected $isLog = true;

    // 有效期
    protected $jobExpireTime;

    // job有效时间(秒)
    protected $jobExpireSeconds = 86400;

    // 重试配置（秒）
    protected $jobRetryConfig = [60, 180];

    protected $className;

    public abstract function execute();

    /**
     * @override
     * @return array
     */
    protected function allowReadFields()
    {
        return ['jobSn', 'isLog', 'jobExpireTime', 'jobExpireSeconds', 'jobRetryConfig', 'className'];
    }

    /**
     * @override
     * @return array
     */
    protected function allowInitFields()
    {
        return $this->allowReadFields();
    }

    public function prepare()
    {
        $this->className = get_class($this);
        $this->jobExpireTime = time() + $this->jobExpireSeconds;
        $this->jobSn = uniqid('job');
    }

    public function getJobSn()
    {
        return $this->jobSn;
    }

    /**
     * 是否已过期
     * @return bool
     */
    public function isExpired()
    {
        return time() >= $this->jobExpireTime;
    }

    public function isLog()
    {
        return $this->isLog;
    }

    /**
     * 下次重试间隔时间，返回0不重试
     *
     * @param int $retryCount
     * @return int
     */
    public function getNextRetryDelay(int $retryCount): int
    {
        if ($retryCount === null) {
            return 0;
        }

        return $this->jobRetryConfig[$retryCount] ?? 0;
    }

    /**
     * 获取失败需要重试的次数
     * @return int
     */
    public function getNeedAttempt(): int
    {
        return count($this->jobRetryConfig);
    }

}
