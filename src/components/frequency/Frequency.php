<?php
namespace app\micro\components;


use Ets\base\Component;
use Ets\consts\LogCategoryConst;
use Ets\Ets;
use Ets\pool\connector\RedisConnector;

class Frequency extends Component
{
    protected $redisComponent = RedisConnector::class;

    private $errorLogTime = 0;

    // 健值分散到集群，拆分数量  checkQps有效
    protected $clusterSplitCount = 16;

    private $splitIndex = 1;

    /**
     * @return RedisConnector
     */
    protected function getRedis()
    {
        return Ets::component($this->redisComponent);
    }


    /**
     * 分散健值计算调用次数，轮询模式
     *
     * @param $key
     * @param int $expire
     * @return mixed
     */
    protected function splitPlusCount($key, $expire = 3)
    {
        if ($this->clusterSplitCount <= 1) {
            $splitKey = $key;
        } else {
            $splitKey = $key . '-' . $this->splitIndex;
            $this->splitIndex++;
            if ($this->splitIndex > $this->clusterSplitCount) {
                $this->splitIndex = 1;
            }
        }

        $count = $this->getRedis()->incr($splitKey);
        if ($count == 1) {
            $this->getRedis()->expire($splitKey, $expire);
        }

        return $count * $this->clusterSplitCount;
    }

    /**
     * 检查当前qps是否大于某个值
     *
     * @param string $keyName
     * @param int $qps  并发值
     * @param bool $errorReturn redis报错时返回值
     * @return bool
     */
    public function checkQps(string $keyName, int $qps = 1000, bool $errorReturn = false): bool
    {
        try {
            $key = 'FrequencyCheckQps-' . time() . '-' . $keyName;

            $count = $this->splitPlusCount($key, 3);

            return $count > $qps;

        } catch (\Throwable $e) {

            $this->logError('Frequency::checkQps异常：', $e);

            return $errorReturn;
        }
    }


    /**
     * 获取x秒内的请求次数
     *
     * @param $keyName
     * @param int $seconds
     * @return int
     */
    public function count(string $keyName, int $seconds = 30): int
    {
        try {
            $key = 'FrequencyCount-'. $keyName;

            $count = $this->getRedis()->incr($key);
            if ($count == 1) {
                $this->getRedis()->expire($key, $seconds);
            }

            return $count;

        } catch (\Throwable $e) {

            $this->logError('Frequency::count异常：', $e);

            return 0;
        }
    }

    protected function logError(string $title, \Throwable $e)
    {
        // 每60秒执行一次
        if (time() - $this->errorLogTime > 60) {
            $this->errorLogTime = time();

            Ets::error($title . $e->getMessage() . $e->getTraceAsString(), LogCategoryConst::ERROR_ALARM);
        }
    }

    /**
     * 获取某个key的计数
     *
     * @param string $keyName
     * @return int
     */
    public function getCount(string $keyName): int
    {
        return  (int) $this->getRedis()->get($keyName);
    }

    /**
     * 获取x天内的请求次数
     *
     * @param string $keyName
     * @param int $days
     * @return int
     */
    public function countDay(string $keyName, int $days = 1): int
    {
        try {
            $key = 'FrequencyCountDay-'. $keyName;

            $count = $this->getRedis()->incr($key);
            if ($count == 1) {
                $this->getRedis()->expireat($key, strtotime(date('Ymd')) + $days * 86400);
            }

            return $count;

        } catch (\Throwable $e) {

            $this->logError('Frequency::countDay异常：', $e);

            return 0;
        }

    }

}
