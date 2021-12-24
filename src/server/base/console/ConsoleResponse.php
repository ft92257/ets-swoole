<?php

namespace Ets\server\base\console;


use Ets\base\BaseObject;
use Ets\server\base\ResponseInterface;

class ConsoleResponse extends BaseObject implements ResponseInterface
{
    /**
     * @var String
     */
    protected $output = '';

    /**
     *
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return [];
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * 请求结束处理
     * @param string $output
     */
    public function finish(string $output)
    {
        $this->output = $output;
    }

}