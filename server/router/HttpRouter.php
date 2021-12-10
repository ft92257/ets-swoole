<?php

namespace Ets\server\router;

use Ets\base\Component;
use Ets\helper\RouterHelper;
use Ets\server\base\http\HttpRequest;

class HttpRouter extends Component implements RouterInterface
{

    /**
     * 解析路由
     *
     * @param HttpRequest $request
     * @return RouterResult
     */
    public function parse($request) :RouterResult
    {
        $arr = explode('/', trim($request->getServerValue('path_info'), '/'));
        $action = array_pop($arr);
        $action = $action ?: 'index';
        $method = 'action' . RouterHelper::line2camel($action);
        $className = RouterHelper::buildController($arr);

        return new RouterResult([
            'className' => $className,
            'method' => $method,
            'body' => ''
        ]);
    }

}

?>