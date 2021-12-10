<?php

namespace Ets\server\router;

use Ets\base\Component;
use Ets\base\EtsException;
use Ets\helper\RouterHelper;
use Ets\server\base\tcp\TcpRequest;

class SerializeRouter extends Component implements RouterInterface
{

    /**
     * 解析路由
     *
     * @param TcpRequest $request
     * @return RouterResult
     * @throws EtsException
     */
    public function parse($request): RouterResult
    {
        $serializeStr = $request->getData();

        $data = unserialize($serializeStr);

        if (! isset($data['path']) || ! isset($data['body'])) {
            throw new EtsException('路由解析不完整：' . $serializeStr);
        }

        $arr = explode('/', trim($data['path'], '/'));
        $action = array_pop($arr);
        $action = $action ?: 'index';
        $method = 'action' . RouterHelper::line2camel($action);

        $className = RouterHelper::buildController($arr);

        $body = $data['body'];

        return new RouterResult([
            'className' => $className,
            'method' => $method,
            'body' => $body
        ]);
    }

}

?>