<?php
/**
 * 微服务调用基类
 */
namespace Ets\service;

use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Ets\server\handle\service\ServiceHandlerInterface;
use Ets\server\handle\service\DefaultServiceHandler;
use Ets\server\result\JsonResult;
use Ets\service\breaker\Breaker;
use Ets\service\breaker\BreakerInterface;
use Ets\service\client\HttpClient;

abstract class MicroServiceHttpClientBase
{

    /**
     * @return static
     */
    public static function build()
    {
        return new static();
    }

    /**
     * @return BreakerInterface
     */
    protected function getBreaker()
    {
        return Ets::component(Breaker::class, false);
    }

    /**
     * @return ServiceHandlerInterface
     */
    protected function getServiceHandle()
    {
        return Ets::component(ServiceHandlerInterface::class, false);
    }


    /**
     * 默认按host熔断
     *
     * @param HttpClient $httpClient
     * @return string
     */
    protected function getBreakKey(HttpClient $httpClient): string
    {
        return $httpClient->getHost();
    }

    /**
     * @param \Throwable $e
     * @param $client
     */
    protected function fallback(\Throwable $e, HttpClient $client)
    {
        ToolsHelper::throws("调用微服务失败！");
    }

    /**
     * @param HttpClient $httpClient
     * @param $method = post
     * @return string
     */
    protected function callApi(
        HttpClient $httpClient,
        $method = HttpClient::METHOD_POST
    ): string {

        $breaker = $this->getBreaker();
        $breakKey = $this->getBreakKey($httpClient);
        if ($breaker && $breaker->isBreaking($breakKey)) {

            $e = new EtsException("系统熔断中");

            $this->fallback($e, $httpClient);
            return '';
        }

        try {

            $serviceHandle = $this->getServiceHandle();
            if ($serviceHandle) {
                $serviceHandle->beforeCallHttpMicroService($httpClient);
            }

            return $httpClient->$method();

        } catch (\Throwable $e) {

            if ($breaker && $e instanceof EtsException) {
                $breaker->addError($breakKey);
            }

            $this->fallback($e, $httpClient);
            return '';
        }
    }

    protected function getData($result)
    {
        return JsonResult::build($result)->checkError()->getData();
    }

}
