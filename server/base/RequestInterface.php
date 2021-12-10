<?php

namespace Ets\server\base;


use Ets\server\router\RouterResult;

interface RequestInterface
{
    public function setRouterInfo(RouterResult $routeResult);
}