<?php
namespace Ets\components\cache;

interface CacheInterface
{

    public function exists($key);

    public function add($key, $value, $expire);

    public function set($key, $value, $expire);

    public function get($key);

    public function delete($key);

}