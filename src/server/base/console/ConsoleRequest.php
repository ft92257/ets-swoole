<?php

namespace Ets\server\base\console;


use Ets\base\BaseObject;
use Ets\server\base\RequestInterface;
use Ets\server\router\RouterResult;

class ConsoleRequest extends BaseObject implements RequestInterface
{
    protected $commandParams;

    protected $controllerClass;

    protected $method;

    /**
     *
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['commandParams', 'controllerClass', 'method'];
    }

    public function getCommandParam(int $index, $default = null)
    {
        return $this->commandParams[$index] ?? $default;
    }

    public function getCommandParams()
    {
        return $this->commandParams;
    }

    public function setRouterInfo(RouterResult $routeResult)
    {
        $this->method = $routeResult->method;
        $this->controllerClass = $routeResult->controllerClass;
    }


}