<?php
namespace Ets\pool;


class RangePool extends Pool
{
    // 连接池数量
    protected $minCount = 2;

    protected $maxCount = 20;

    // 每秒请求数超过该值则升档
    protected $qpsThreshold = 20;

    protected $frequencyData;

    protected $nextRecycleTime;


    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['minCount', 'maxCount', 'qpsThreshold'];
    }

    /**
     * 检查请求频率, 更新连接池数量
     * @Override
     */
    protected function beforeConnect()
    {
        $now = time();
        if (isset($this->frequencyData[$now])) {
            $this->frequencyData[$now]++;
            if ($this->frequencyData[$now] > $this->qpsThreshold) {

                if ($this->count < $this->maxCount) {
                    // 升档，取消回收
                    $this->nextRecycleTime = null;
                }

                $this->count = $this->maxCount;
            } else {
                if ($this->count > $this->minCount) {
                    // 降档, 准备60秒后回收
                    $this->nextRecycleTime = time() + 60;
                }

                $this->count = $this->minCount;
            }

        } else {
            // 重置数据
            $this->frequencyData = [
                $now => 1
            ];
        }

        $this->checkRecycle();
    }


    /**
     * 检查回收
     */
    private function checkRecycle()
    {
        if ($this->nextRecycleTime == null) {
            return;
        }

        if ($this->nextRecycleTime > time()) {
            // 未到时间
            return;
        }

        $begin = $this->count + 1;
        for ($i=$begin;$i<=$this->maxCount;$i++) {
            if (! empty($this->connections[$i])) {
                $this->connections[$i]->close();
            }

            unset($this->connections[$i]);
        }
    }

}