<?php

namespace Ets\queue;


use Ets\base\BaseObject;

class Loop extends BaseObject
{
    public $maxRunningCount = 5;

    protected $runningCount = 0;

    // 上次被拦截时间戳
    protected $interceptTime = 0;

    // 被拦截300秒后自动重置
    protected $resetExpire = 300;

    public function isAllowRunning()
    {
        if ($this->runningCount >= $this->maxRunningCount) {
            // 被拦截
            if (! $this->interceptTime) {
                $this->interceptTime = time();
            } else {
                if (time() > $this->interceptTime + $this->resetExpire) {
                    // 超期重置
                    $this->runningCount = 0;
                }
            }
        } else {
            // 未拦截, 重置拦截时间
            $this->interceptTime = 0;
        }

        return $this->runningCount < $this->maxRunningCount;
    }

    public function setRunning()
    {
        $this->runningCount++;
    }

    public function finishRunning()
    {
        $this->runningCount--;

        if ($this->runningCount < 0) {
            $this->runningCount = 0;
        }
    }

}
