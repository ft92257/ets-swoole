<?php
namespace Ets\components\cache;

use Ets\base\Component;
use Ets\consts\LogCategoryConst;
use Ets\Ets;
use Ets\pool\wrapper\RedisWrapper;

class RedisCache extends Component implements CacheInterface
{
    protected $redisComponent = RedisWrapper::class;

    protected $keyPrefix = '';

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['redisComponent', 'keyPrefix'];
    }

    /**
     * @return RedisWrapper
     */
    protected function getRedis()
    {
        return Ets::component($this->redisComponent);
    }

    protected function buildKey($key)
    {
        if (is_string($key)) {
            $key = ctype_alnum($key) && mb_strlen($key, '8bit') <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }

        return $this->keyPrefix . $key;
    }

    public function exists($key)
    {
        try {

            $cacheKey = $this->buildKey($key);

            return $this->getRedis()->exists($cacheKey);

        } catch (\Throwable $e) {
            Ets::error('Redis异常：' . $e->getMessage() . $e->getTraceAsString(), LogCategoryConst::ERROR_REDIDS);

            return false;
        }
    }

    public function get($key)
    {
        try {

            $cacheKey = $this->buildKey($key);

            $result = $this->getRedis()->get($cacheKey);

            if (is_string($result)) {
                $value = unserialize($result);
            } else {
                $value = $result;
            }

            return $value[0] ?? $value;

        } catch (\Throwable $e) {
            Ets::error('Redis异常：' . $e->getMessage() . $e->getTraceAsString(), LogCategoryConst::ERROR_REDIDS);

            return false;
        }
    }

    public function set($key, $value, $expire = 0)
    {
        try {

            $cacheKey = $this->buildKey($key);

            $value = serialize([$value, null]);

            if (is_numeric($expire) && $expire) {
                return $this->getRedis()->set($cacheKey, $value, $expire);
            } else {
                return $this->getRedis()->set($cacheKey, $value);
            }

        } catch (\Throwable $e) {
            Ets::error('Redis异常：' . $e->getMessage() . $e->getTraceAsString(), LogCategoryConst::ERROR_REDIDS);

            return false;
        }
    }

    public function add($key, $value, $expire = 0)
    {
        try {

            $cacheKey = $this->buildKey($key);

            $value = serialize([$value, null]);

            if (is_numeric($expire) && $expire) {
                return $this->getRedis()->set($cacheKey, $value, ['nx', 'ex' => $expire]);
            } else {
                return $this->getRedis()->set($cacheKey, $value, ['nx']);
            }

        } catch (\Throwable $e) {
            Ets::error('Redis异常：' . $e->getMessage() . $e->getTraceAsString(), LogCategoryConst::ERROR_REDIDS);

            return false;
        }
    }

    public function delete($key)
    {
        try {

            $cacheKey = $this->buildKey($key);

            return $this->getRedis()->del($cacheKey);

        } catch (\Throwable $e) {
            Ets::error('Redis异常：' . $e->getMessage() . $e->getTraceAsString(), LogCategoryConst::ERROR_REDIDS);

            return false;
        }
    }

}