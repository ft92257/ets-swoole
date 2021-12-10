<?php

namespace Ets\server\router;


use Ets\base\Component;
use Ets\helper\RouterHelper;
use Ets\server\base\console\ConsoleRequest;

class ConsoleRouter extends Component implements RouterInterface
{
    /**
     * @param ConsoleRequest $request
     * @return RouterResult
     */
    public function parse($request): RouterResult
    {
        $param = $request->getCommandParams();

        $arr = explode('/', trim($param[0], '/'));
        $action = array_pop($arr);
        $method = 'action' . RouterHelper::line2camel($action);
        $className = RouterHelper::buildController($arr);

        return new RouterResult([
            'className' => $className,
            'method' => $method,
            'body' => ''
        ]);
    }

}