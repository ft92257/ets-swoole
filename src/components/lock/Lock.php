<?php

namespace Ets\components\lock;


use Ets\base\Component;
use Ets\components\cache\RedisCache;
use Ets\Ets;

class Lock extends Component
{
    protected $cacheComponent = RedisCache::class;

    const KEY_PRE = 'Lock:';

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['cacheComponent'];
    }

    /**
     * @return RedisCache
     */
    protected function getCache()
    {
        return Ets::component($this->cacheComponent);
    }

    public function checkLock($key)
    {
        $cacheKey = self::KEY_PRE . $key;

        return $this->getCache()->get($cacheKey);
    }

    public function addLock($key, $expire = 5)
    {
        $cacheKey = self::KEY_PRE . $key;

        return $this->getCache()->add($cacheKey, 1, $expire);
    }

    /**
     * 解锁
     * @param $key
     */
    public function unlock($key)
    {
        $cacheKey = self::KEY_PRE . $key;

        $this->getCache()->delete($cacheKey);
    }

}