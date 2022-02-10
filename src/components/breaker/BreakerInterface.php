<?php

namespace Ets\components\breaker;


interface BreakerInterface
{

    /**
     * 是否熔断中
     * @param $key
     * @return bool
     */
    public function isBreaking($key): bool;

    /**
     * 错误处理
     * @param $key
     */
    public function addError($key);

    /**
     * 处理成功
     * @param $key
     * @return mixed
     */
    public function addSuccess($key);

}