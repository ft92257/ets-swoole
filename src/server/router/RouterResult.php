<?php

namespace Ets\server\router;

use Ets\base\BaseObject;

class RouterResult extends BaseObject
{
    // 不包含包名的类名
    public $className;

    // 方法名
    public $method;

    // 参数内容
    public $body;

    public $controllerClass;

    protected function allowInitFields()
    {
        return ['className', 'method', 'body'];
    }

}

?>