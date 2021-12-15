<?php

namespace Ets\server;

use Ets\base\Component;
use Ets\server\base\Controller;
use Ets\base\EtsException;
use Ets\consts\EtsConst;
use Ets\Ets;
use Ets\coroutine\CoroutineVar;
use Ets\pool\connector\BasePoolConnector;
use Ets\server\base\RequestInterface;
use Ets\server\base\ResponseInterface;
use Ets\server\errorHandle\ErrorHandlerInterface;
use Ets\server\result\ResultInterface;
use Ets\server\router\RouterInterface;

abstract class BaseServer extends Component
{
    protected $controllerPackagePath;

    protected $routerComponent;

    protected $errorHandlerComponent;

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
            if (! class_exists($class) || ! method_exists($class, $method)) {

                $output = Ets::$app->isDebug() ? $class . '::' . $method . ' not exists' : '404';

                throw new EtsException($output, EtsConst::RESULT_CODE_SYSTEM_ERROR);
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

        } catch (\Throwable $e) {

            $this->handleError($e, $response);

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