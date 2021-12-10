<?php

namespace Ets\server\router;

use Ets\base\Component;
use Ets\helper\RouterHelper;
use Ets\server\base\tcp\TcpRequest;

class JsonRouter extends Component implements RouterInterface
{

    /**
     * 解析路由
     *
     * @param TcpRequest $request
     * @return RouterResult
     */
    public function parse($request) :RouterResult
    {
        $data = json_decode($request->getData(), true);

        $arr = explode('/', trim($data[0], '/'));
        $action = array_pop($arr);
        $action = $action ?: 'index';
        $method = 'action' . RouterHelper::line2camel($action);

        $className = RouterHelper::buildController($arr);

        $body = is_string($data[1]) ? trim($data[1]) : $data[1];

        return new RouterResult([
            'className' => $className,
            'method' => $method,
            'body' => $body
        ]);
    }

}

?>