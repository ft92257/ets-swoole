<?php
namespace Swoole\Coroutine;

class Redis
{
    private $redis;
    public $connected = false;

    public function __construct()
    {
        $this->redis = new \Redis();
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        $ret = call_user_func_array([$this->redis, $name], $arguments);
        if ($name == 'connect') {
            $this->connected = true;
        }

        return $ret;
    }

}
