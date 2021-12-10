<?php
namespace Ets\coroutine;

use Ets\base\BaseObject;
use Swoole\Coroutine;

class CoroutineVar
{
    /**
     * 协程数组链表
     * @var CoroutineArrayList[]
     */
    private static $coroutineArrayLists = [];

    /**
     * @var CoroutineHashMap[]
     */
    private static $coroutineHashMaps = [];

    /**
     * @var BaseObject[]
     */
    private static $coroutineObjects = [];

    /**
     * 获取当前协程id
     * @return int
     */
    public static function getCid()
    {
        return Coroutine::getuid();
    }

    /**
     *
     * @param $key string 需防止重名
     * @return CoroutineArrayList
     */
    public static function getArrayList($key)
    {
        if (! isset(self::$coroutineArrayLists[self::getCid()][$key])) {
            self::$coroutineArrayLists[self::getCid()][$key] = new CoroutineArrayList();
        }

        return self::$coroutineArrayLists[self::getCid()][$key];
    }

    /**
     * @param $key
     * @return CoroutineHashMap
     */
    public static function getHashMap($key)
    {
        if (! isset(self::$coroutineHashMaps[self::getCid()][$key])) {
            self::$coroutineHashMaps[self::getCid()][$key] = new CoroutineHashMap();
        }

        return self::$coroutineHashMaps[self::getCid()][$key];
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function getObject($key)
    {
        return self::$coroutineObjects[self::getCid()][$key] ?? null;
    }

    /**
     * @param $key
     * @param BaseObject $object
     */
    public static function setObject($key, $object)
    {
        self::$coroutineObjects[self::getCid()][$key] = $object;
    }

    /**
     * 清空当前线程的自定义全局变量
     */
    public static function clear()
    {
        unset(self::$coroutineArrayLists[self::getCid()]);
        unset(self::$coroutineHashMaps[self::getCid()]);
        unset(self::$coroutineObjects[self::getCid()]);
    }


}