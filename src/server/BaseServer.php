<?php

namespace Ets\server;

use Ets\base\Component;
use Ets\event\EventHelper;
use Ets\event\events\RequestBeforeEvent;
use Ets\event\events\RequestErrorEvent;
use Ets\event\events\RequestFinishEvent;
use Ets\helper\ToolsHelper;
use Ets\server\base\Controller;
use Ets\base\EtsException;
use Ets\consts\EtsConst;
use Ets\Ets;
use Ets\coroutine\CoroutineVar;
use Ets\pool\connector\BasePoolConnector;
use Ets\server\base\RequestInterface;
use Ets\server\base\ResponseInterface;
use Ets\server\handle\error\ErrorHandlerInterface;
use Ets\server\handle\request\DefaultRequestHandler;
use Ets\server\handle\request\RequestHandlerInterface;
use Ets\server\result\ResultInterface;
use Ets\server\router\RouterInterface;

abstract class BaseServer extends Component
{
    protected $controllerPackagePath;

    protected $routerComponent;

    protected $errorHandlerComponent;

    protected $requestHandleComponent = DefaultRequestHandler::class;

    protected $appConfig = [
        'timeZone' => 'Asia/Shanghai',
        'env' => 'dev',
        'name' => 'Demo',
        'debug' => true
    ];

    protected $coroutineSetting = [
        'socket_connect_timeout' => 3,
        'socket_timeout' => 10,
        'enable_preemptive_scheduler' => 1,
    ];


    public abstract function run();

    /**
     * @param ResponseInterface $response
     */
    public abstract function response($response);

    public function setAppConfig($appConfig)
    {
        $this->appConfig = array_merge($this->appConfig, $appConfig);
    }

    public function getAppConfigValue($key, $default = null)
    {
        return $this->appConfig[$key] ?? $default;
    }

    public function setCoroutineSetting($coroutineSetting)
    {
        $this->coroutineSetting = array_merge($this->coroutineSetting, $coroutineSetting);
    }

    public function setRequestHandleComponent($requestHandleComponent)
    {
        $this->requestHandleComponent = $requestHandleComponent;
    }

    /**
     * @return RequestHandlerInterface
     */
    protected function getRequestHandle()
    {
        return Ets::component($this->requestHandleComponent, false);
    }

    /**
     * @return RouterInterface
     * @throws EtsException
     */
    protected function getRouter()
    {
        return Ets::component($this->routerComponent);
    }

    protected function getControllerPackagePath()
    {
        return $this->controllerPackagePath;
    }

    /**
     * @return ErrorHandlerInterface
     */
    protected function getErrorHandler()
    {
        return Ets::component($this->errorHandlerComponent);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function handler($request, $response)
    {
        // 路由解析
        try {
            // 执行方法
            $routerResult = $this->getRouter()->parse($request);

            $class = $this->getControllerPackagePath() . $routerResult->className;
            $routerResult->controllerClass = $class;
            $method = $routerResult->method;

            $request->setRouterInfo($routerResult);

            // 路由检测
            if (! ToolsHelper::classExists($class) || ! method_exists($class, $method)) {

                $output = Ets::$app->isDebug() ? $class . '::' . $method . ' not exists' : '404';

                throw new EtsException($output, EtsConst::RESULT_CODE_SYSTEM_ERROR);
            }

            // 请求执行前事件
            EventHelper::localTrigger(new RequestBeforeEvent([
                'request' => $request
            ]));

            if ($this->getRequestHandle()) {
                $this->getRequestHandle()->beforeRequest($request);
            }

            /**
             * @var $controller Controller
             */
            $controller = (new $class([
                'request' => $request,
                'response' => $response,
            ]));

            /**
             * @var ResultInterface $result
             */
            $controller->$method();

            // 请求执行成功事件
            EventHelper::localTrigger(new RequestFinishEvent([
                'request' => $request,
                'response' => $response,
            ]));

        } catch (\Throwable $e) {

            $this->handleError($e, $response);

            // 请求执行异常事件
            EventHelper::localTrigger(new RequestErrorEvent([
                'request' => $request,
                'response' => $response,
                'exception' => $e
            ]));
        }

        try {
            $this->response($response);

            // 释放连接池
            $wrappers = CoroutineVar::getArrayList(EtsConst::COROUTINE_POOL_WRAPPERS);
            /**
             * @var $wrapper BasePoolConnector
             */
            foreach ($wrappers->getValues() as $wrapper) {

                $wrapper->freeUse();
            }

            // 写日志
            Ets::getLogger()->flush();

            // 释放当前协程的自定义全局变量内存
            CoroutineVar::clear();

        } catch (\Throwable $e) {
            //
            print_r($e->getMessage());
        }

    }

    /**
     * 请求异常处理
     * @param \Throwable $e
     * @param ResponseInterface $response
     */
    public function handleError(\Throwable $e, $response)
    {
        try {
            // 错误处理
            $handle = $this->getErrorHandler();

            $output = $handle->handleException($e, $response)->getOutputString();

        } catch (\Throwable $err) {

            $output = '框架底层异常：' . $err->getMessage() . $err->getTraceAsString() . ' 原始错误：' . $e->getMessage();
        }

        $response->finish($output);
    }

}