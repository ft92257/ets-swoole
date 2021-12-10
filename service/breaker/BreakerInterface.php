<?php

namespace Ets\service\breaker;


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

}