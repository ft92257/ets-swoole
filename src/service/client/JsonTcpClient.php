<?php

namespace Ets\service\client;


use Ets\helper\ToolsHelper;

class JsonTcpClient extends TcpClient
{
    protected $path;

    protected $params;

    public function setParams(string $path, array $params)
    {
        $this->body = ToolsHelper::toJson([
            $path,
            ToolsHelper::toJson($params)
        ]);

        return $this;
    }

}
