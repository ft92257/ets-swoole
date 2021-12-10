<?php
namespace Ets\coroutine;

use Ets\base\BaseArrayObject;

class CoroutineHashMap extends BaseArrayObject
{
    private $mapData = [];

    public function put($key, $value)
    {
        $this->mapData[$key] = $value;
    }

    public function get($key)
    {
        return $this->mapData[$key] ?? null;
    }

}