<?php

namespace Ets\server;

use Ets\server\base\tcp\TcpRequest;
use Ets\server\base\tcp\TcpResponse;
use Ets\server\errorHandle\DefaultErrorHandler;
use Ets\server\router\JsonRouter;
use Swoole\Coroutine;

class TcpServer extends BaseServer
{

    protected $controllerPackagePath = '\\application\\tcp\\controller\\';

    protected $port = 8003;

    protected $host = '0.0.0.0';

    protected $routerComponent = JsonRouter::class;

    protected $errorHandlerComponent = DefaultErrorHandler::class;

    protected $setting = [
        'worker_num' => 4,
        'reactor_num' => 4,
        'max_connection' => 30000,
        'max_coroutine' => 30000,
        'buffer_output_size' =>  32 * 1024 * 1024,
        'package_max_length' => 32 * 1024 * 1024,
        'open_eof_check' => true,
        'package_eof' => "\r\n",
    ];

    protected function allowInitFields()
    {
        return ['controllerPackagePath', 'routerComponent', 'errorHandlerComponent', 'port', 'host'];
    }

    public function setSetting($setting)
    {
        $this->setting = array_merge($this->setting, $setting);
    }

    /**
     * @param TcpResponse $response
     */
    public function response($response)
    {
        try {
            $response->getSwooleServer()->send($response->getFd(), $response->getOutput() . "\n");
        } catch (\Throwable $e) {
            //
        }
    }

    public function run()
    {
        date_default_timezone_set($this->appConfig['timeZone']);

        Coroutine::set($this->coroutineSetting);

        $server = new \swoole_server($this->host, $this->port);

        $server->set($this->setting);

        $server->on('Receive', function (\swoole_server $swooleServer, int $fd, int $reactorId, string $data) {

            $tcpRequest = new TcpRequest([
                'data' => $data
            ]);
            $tcpResponse = new TcpResponse([
                'swooleServer' => $swooleServer,
                'fd' => $fd,
                'reactorId' => $reactorId
            ]);

            $this->handler($tcpRequest, $tcpResponse);
        });

        $server->start();
    }

}
