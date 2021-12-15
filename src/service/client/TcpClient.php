<?php

namespace Ets\service\client;


use Ets\base\BaseObject;
use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Swoole\Coroutine\Client;

class TcpClient extends BaseObject
{
    protected $host;

    protected $port;

    protected $body;

    protected $timeout = 3;

    protected $ending = "\r\n";

    // 日志记录几率百分比
    protected $logPercent = 100;

    // 日志分类
    protected $logCategory = 'tcp';

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return [
            'host', 'port', 'body', 'timeout', 'ending', 'logPercent', 'logCategory'
        ];
    }

    public function getUrl()
    {
        return $this->host . ':' . $this->port;
    }

    public function callTcp()
    {
        $isLog = ToolsHelper::checkPercent($this->logPercent);
        $reqSn = uniqid('tcp');
        if ($isLog) {
            Ets::info([
                'reqSn' => $reqSn,
                'url' => $this->getUrl(),
                'body' => $this->body,
            ], $this->logCategory);
        }

        $client = new Client(SWOOLE_SOCK_TCP);
        if (! $client->connect($this->host, $this->port, $this->timeout))
        {
            $e = new EtsException('Tcp连接失败，请稍后再试：' . $this->host . ':' . $this->port);

            if ($isLog) {
                Ets::error([
                    'reqSn' => $reqSn,
                    'error' => $e->getMessage() . '#' . $e->getTraceAsString(),
                ], $this->logCategory);
            }

            throw $e;
        }

        if ($this->ending) {
            $this->body .= $this->ending;
        }

        try {

            $client->send($this->body);
            $ret = $client->recv();
            $client->close();

        } catch (\Throwable $e) {

            if ($isLog) {
                Ets::error([
                    'reqSn' => $reqSn,
                    'error' => $e->getMessage() . '#' . $e->getTraceAsString(),
                ], $this->logCategory);
            }

            throw $e;
        }

        if ($isLog) {
            Ets::info([
                'reqSn' => $reqSn,
                'result' => $ret,
            ], $this->logCategory);
        }

        return $ret;
    }
}