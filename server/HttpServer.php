<?php

namespace Ets\server;

use Ets\server\base\http\HttpRequest;
use Ets\server\base\http\HttpResponse;
use Ets\server\errorHandle\HttpJsonErrorHandler;
use Ets\server\router\HttpRouter;
use Swoole\Coroutine;

class HttpServer extends BaseServer
{
    protected $controllerPackagePath = '\\application\\controller\\';

    protected $port = 8001;

    protected $host = '0.0.0.0';

    protected $routerComponent = HttpRouter::class;

    protected $errorHandlerComponent = HttpJsonErrorHandler::class;

    protected $setting = [
        'worker_num' => 4,
        'reactor_num' => 4,
        'max_connection' => 30000,
        'max_coroutine' => 30000,
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
     * @param HttpResponse $response
     */
    public function response($response)
    {
        try {
            $swooleResponse = $response->getSwooleResponse();
            if (! is_object($swooleResponse)) {
                return;
            }

            if (! empty($response->getHeaders())) {
                foreach ($response->getHeaders() as $key => $value) {
                    $swooleResponse->header($key, $value);
                }
            }

            $swooleResponse->status($response->getHttpCode());

            $swooleResponse->end($response->getOutput());

        } catch (\Throwable $e) {
            //
        }
    }

    public function run()
    {
        date_default_timezone_set($this->appConfig['timeZone']);

        Coroutine::set($this->coroutineSetting);

        $server = new \swoole_http_server($this->host, $this->port);

        $server->set($this->setting);

        $server->on('request', function ($request, $response) {

            if ($request->server['request_uri'] == '/favicon.ico' || $request->server['path_info'] == '/favicon.ico') {
                return;
            }

            $httpRequest = new HttpRequest([
                'swooleRequest' => $request,
            ]);

            $httpResponse = new HttpResponse([
                'swooleResponse' => $response
            ]);

            $this->handler($httpRequest, $httpResponse);
        });

        $server->start();
    }

}
