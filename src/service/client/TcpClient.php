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

    protected $requestEof = "\r\n\r\n";

    protected $packageSetting = [
        'open_eof_check' => true,
        'package_eof' => "\r\n\r\n",
        'package_max_length' => 1024 * 1024 * 32,
    ];

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
            'host', 'port', 'body', 'timeout', 'requestEof', 'packageSetting', 'logPercent', 'logCategory'
        ];
    }

    /**
     * @param array $config
     * @return static
     */
    public static function build($config = [])
    {
        return new static($config);
    }

    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    public function setPort(string $port)
    {
        $this->port = $port;

        return $this;
    }

    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
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

        $setting = $this->packageSetting;
        $setting['timeout'] = $this->timeout;

        $client->set($setting);

        if (! $client->connect($this->host, $this->port, 3))
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

        if ($this->requestEof) {
            $this->body .= $this->requestEof;
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