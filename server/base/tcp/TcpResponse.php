<?php

namespace Ets\server\base\tcp;


use Ets\base\BaseObject;
use Ets\server\base\ResponseInterface;

class TcpResponse extends BaseObject implements ResponseInterface
{
    /**
     * @var \swoole_server $swooleServer
     */
    protected $swooleServer;

    /**
     * @var int $fd
     */
    protected $fd;

    /**
     * @var int $reactorId
     */
    protected $reactorId;

    /**
     * @var String
     */
    protected $output;


    /**
     *
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['swooleServer', 'fd', 'reactorId'];
    }

    /**
     * @return \swoole_server
     */
    public function getSwooleServer()
    {
        return $this->swooleServer;
    }

    public function getFd()
    {
        return $this->fd;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * 请求结束处理
     * @param $output
     */
    public function finish(string $output)
    {
        $this->output = $output;
    }

}