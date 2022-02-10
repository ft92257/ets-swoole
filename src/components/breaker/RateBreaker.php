<?php

namespace Ets\components\breaker;

use Ets\base\Component;

class RateBreaker extends Component implements BreakerInterface
{

    /**
     * @var int $internal 计数间隔（秒）
     */
    protected $countInternal = 30;

    // 错误率比例超过该值则触发熔断
    protected $errorRate = 0.1;

    /**
     * @var int $breakSecond 熔断持续时间（秒）
     */
    protected $breakSecond = 180;

    private $data = [];

    protected function allowInitFields()
    {
        return ['countInternal', 'errorRate', 'breakSecond'];
    }


    /**
     * 是否熔断中
     * @param $key
     * @return bool
     */
    public function isBreaking($key): bool
    {
        if (empty($this->data[$key]['breakingTimeEnd'])) {
            return false;
        } else {
            // 未过期则返回熔断中
            return time() <= $this->data[$key]['breakingTimeEnd'];
        }
    }

    /**
     * 增加错误次数
     * @param $key
     */
    public function addError($key)
    {
        $this->addCount($key, true);
    }

    public function addSuccess($key)
    {
        $this->addCount($key, false);
    }

    protected function addCount($key, $isError)
    {
        $initData = [
            'failCount' => $isError ? 1 : 0,
            'successCount' => $isError ? 0 : 1,
            'beginTime' => time(),
        ];

        if (empty($this->data[$key])) {
            $this->data[$key] = $initData;
        } else {
            if ($this->data[$key]['beginTime'] < (time() - $this->countInternal)) {
                // 已过期，重新计数
                $this->data[$key] = $initData;
            } else {
                if ($isError) {
                    $this->data[$key]['failCount']++;
                } else {
                    $this->data[$key]['successCount']++;
                }
            }
        }

        // 触发熔断
        $total = $this->data[$key]['failCount'] + $this->data[$key]['successCount'];
        if ($this->data[$key]['failCount'] / $total >= $this->errorRate) {

            $this->data[$key]['breakingTimeEnd'] = time() + $this->breakSecond;
        }
    }

}