<?php

namespace Ets\server;

use Ets\server\base\console\ConsoleRequest;
use Ets\server\base\console\ConsoleResponse;
use Ets\server\handle\error\DefaultErrorHandler;
use Ets\server\router\ConsoleRouter;

class ConsoleServer extends BaseServer
{

    protected $controllerPackagePath = '\\application\\console\\controller\\';

    protected $routerComponent = ConsoleRouter::class;

    protected $errorHandlerComponent = DefaultErrorHandler::class;

    protected function allowInitFields()
    {
        return [
            'controllerPackagePath', 'routerComponent', 'errorHandlerComponent'
        ];
    }

    /**
     * @param ConsoleResponse $response
     */
    public function response($response)
    {
        echo $response->getOutput();
    }

    public function run()
    {
        date_default_timezone_set($this->appConfig['timeZone']);

        array_splice($GLOBALS['argv'], 1,0, 'console');
        $args = $GLOBALS['argv'];

        go(function () use ($args) {
            $data = array_slice($args,2);

            $consoleRequest = new ConsoleRequest([
                'commandParams' => $data
            ]);
            $consoleResponse = new ConsoleResponse();

            $this->handler($consoleRequest, $consoleResponse);
        });
    }

}