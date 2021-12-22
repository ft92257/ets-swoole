<?php
namespace Ets\pool\connector;
use Ets\base\EtsException;
use Swoole\Coroutine\Redis;

class RedisConnector extends BasePoolConnector
{

    protected $host;

    protected $port;

    protected $timeout = 5;

    protected $password = '';

    protected $database = 0;

    /**
     * @Override
     * @return array
     */
    protected function getSetterFields()
    {
        return [
            'host', 'port', 'password', 'database', 'timeout'
        ];
    }

    protected function allowReadFields()
    {
        return [
            'host', 'port', 'password', 'database', 'timeout'
        ];
    }

    /**
     * 连接redis/重连
     *
     * @return Redis
     * @throws EtsException
     */
    protected function connect()
    {
        $redis = new Redis();

        // 需确保数据和yii一致
        if (! $redis->connect($this->host, $this->port)) {
            throw new EtsException('connect to redis' . $this->host . ':' . $this->port . 'failed ' . $redis->errCode . ' ' . $redis->errMsg);
        }

        if ($this->password && ! $redis->auth($this->password)) {
            throw new EtsException('auth to redis' . $this->host . ':' . $this->port . 'failed ' . $redis->errCode . ' ' . $redis->errMsg);
        }

        $redis->select($this->database);

        return $redis;
    }

    public function close()
    {
        $this->instance->close();
    }

}