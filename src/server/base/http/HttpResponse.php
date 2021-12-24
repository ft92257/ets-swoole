<?php

namespace Ets\server\base\http;


use Ets\base\BaseObject;
use Ets\server\base\ResponseInterface;

class HttpResponse extends BaseObject implements ResponseInterface
{

    protected $swooleResponse;

    /**
     * @var String
     */
    protected $output = '';

    protected $httpCode = 200;

    protected $headers = [];

    /**
     *
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['swooleResponse'];
    }

    public function getSwooleResponse()
    {
        return $this->swooleResponse;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
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