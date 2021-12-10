<?php

namespace Ets\server\base\tcp;


use Ets\base\BaseObject;
use Ets\server\base\RequestInterface;
use Ets\server\router\RouterResult;

class TcpRequest extends BaseObject implements RequestInterface
{
    /**
     * @var string 原始数据
     */
    protected $data;

    protected $body;

    protected $controllerClass;

    protected $method;

    /**
     *
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['data', 'body', 'controllerClass', 'method'];
    }

    public function setRouterInfo(RouterResult $routeResult)
    {
        $this->method = $routeResult->method;
        $this->controllerClass = $routeResult->controllerClass;
        $this->body = $routeResult->body;
    }

    public function getData($urlDecode = true)
    {
        return $urlDecode ? urldecode($this->data) : $this->data;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getJsonPosts()
    {
        $raw =  $this->getBody();
        $ret = $raw ? (json_decode($raw, true) ?: []) : [];

        return $ret;
    }

    public function getJsonParam($key, $default = null)
    {
        $posts = $this->getJsonPosts();

        return $posts[$key] ?? $default;
    }

}