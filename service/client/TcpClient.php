<?php

namespace Ets\service\client;


use Ets\base\BaseObject;
use Ets\base\EtsException;
use Swoole\Coroutine\Client;

class TcpClient extends BaseObject
{
    protected $host;

    protected $port;

    protected $body;

    protected $timeout = 3;

    protected $ending = "\r\n";

    public function getUrl()
    {
        return $this->host . ':' . $this->port;
    }

    public function callTcp()
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        if (! $client->connect($this->host, $this->port, $this->timeout))
        {
            throw new EtsException('Tcp连接失败，请稍后再试：' . $this->host . ':' . $this->port);
        }

        if ($this->ending) {
            $this->body .= $this->ending;
        }

        $client->send($this->body);
        $ret = $client->recv();
        $client->close();

        return $ret;
    }
}