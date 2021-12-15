<?php
namespace Ets\pool\connector;
use Ets\base\EtsException;
use Swoole\Coroutine\MySQL;

class MysqlConnector extends BasePoolConnector
{

    protected $host;

    protected $port;

    protected $username;

    protected $password;

    protected $database;

    protected $charset = 'utf8mb4';

    protected $timeout = 5;

    /**
     * @Override
     * @return array
     */
    protected function getSetterFields()
    {
        return [
            'host', 'port', 'username', 'password', 'database', 'charset', 'timeout'
        ];
    }

    /**
     * 连接数据库/重连
     *
     * @return MySQL
     * @throws EtsException
     */
    protected function connect()
    {
        $mysql = new MySQL();

        if (! $mysql->connect([
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->username,
            'password' => $this->password,
            'database' => $this->database,
            'charset' => $this->charset,
            'timeout' => $this->timeout,
        ])) {
            throw new EtsException('connect to mysql ' . $this->host . ':' . $this->port . ' failed');
        }

        return $mysql;
    }

    public function close()
    {
        $this->instance->close();
    }


}