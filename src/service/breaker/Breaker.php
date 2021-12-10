<?php

namespace Ets\service\breaker;

use Ets\base\Component;

class Breaker extends Component implements BreakerInterface
{

    /**
     * @var int $internal 计数间隔（秒）
     */
    protected $countInternal = 30;

    /**
     * @var int $errorLimit 计数间隔内错误上限，到达该次数后将触发熔断
     */
    protected $errorLimit = 5;

    /**
     * @var int $breakSecond 熔断持续时间（秒）
     */
    protected $breakSecond = 180;

    private static $data = [];


    /**
     * 是否熔断中
     * @param $key
     * @return bool
     */
    public function isBreaking($key): bool
    {
        if (empty(self::$data[$key]['breakingTimeEnd'])) {
            return false;
        } else {
            // 未过期则返回熔断中
            return time() <= self::$data[$key]['breakingTimeEnd'];
        }
    }

    /**
     * 增加错误次数
     * @param $key
     */
    public function addError($key)
    {
        if (empty(self::$data[$key])) {
            self::$data[$key] = [
                'count' => 1,
                'beginTime' => time(),
            ];
        } else {
            if (self::$data[$key]['beginTime'] > time() - $this->countInternal) {
                // 已过期，重新计数
                self::$data[$key] = [
                    'count' => 1,
                    'beginTime' => time(),
                ];
            } else {
                self::$data[$key]['count']++;
            }
        }

        // 触发熔断
        if (self::$data[$key]['count'] >= $this->errorLimit) {

            self::$data[$key]['breakingTimeEnd'] = time() + $this->breakSecond;
        }
    }

}