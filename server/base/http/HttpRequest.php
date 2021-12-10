<?php

namespace Ets\server\base\http;


use Ets\base\BaseObject;
use Ets\server\base\RequestInterface;
use Ets\server\router\RouterResult;

class HttpRequest extends BaseObject implements RequestInterface
{

    protected $swooleRequest;

    protected $controllerClass;

    protected $method;

    /**
     *
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['swooleRequest', 'controllerClass', 'method'];
    }

    public function setRouterInfo(RouterResult $routeResult)
    {
        $this->method = $routeResult->method;
        $this->controllerClass = $routeResult->controllerClass;
    }


    /**
     * $request->header array
     * Http请求的头部信息。类型为数组，所有key均为小写。 $request->header['host']
     *
     * $request->server
     * Http请求相关的服务器信息，相当于PHP的$_SERVER数组。所有key均为小写
     *
     * $request->rawContent()
     * 返回原始POST数据，此函数等同于PHP的fopen('php://input')
     *
     * $_GET = $request->get;
     * $_POST = $request->post;
     * $_COOKIE = $request->cookie;
     * $_FILES = $request->files;
     *
     * @return object $request
     */
    public function getSwooleRequest()
    {
        return $this->swooleRequest;
    }

    public function getServerValue($key, $default = null)
    {
        return $this->getSwooleRequest()->server[$key] ?? $default;
    }

    public function getHeaders()
    {
        return $this->getSwooleRequest()->header;
    }

    public function getBody()
    {
        return $this->getSwooleRequest()->rawContent();
    }

    public function getPosts()
    {
        return $this->getSwooleRequest()->post ?: [];
    }

    public function getQueryParams()
    {
        $gets = $this->getSwooleRequest()->get ?: [];

        return $gets;
    }

    public function getJsonPosts()
    {
        $raw =  $this->getBody();
        $ret = $raw ? (json_decode($raw, true) ?: []) : [];

        return $ret;
    }


    public function getPostParam($key, $default = null)
    {
        $posts = $this->getPosts();

        return $posts[$key] ?? $default;
    }

    public function getJsonParam($key, $default = null)
    {
        $posts = $this->getJsonPosts();

        return $posts[$key] ?? $default;
    }

    public function getQueryParam($key, $default = null)
    {
        $gets = $this->getSwooleRequest()->get ?: [];

        return $gets[$key] ?? $default;
    }

    public function getHeader($key, $default = null)
    {
        return $this->getSwooleRequest()->header[$key] ?? $default;
    }

    public function getRequestId()
    {
        return $this->getHeader('x-request-id') ?: uniqid('request');
    }

}