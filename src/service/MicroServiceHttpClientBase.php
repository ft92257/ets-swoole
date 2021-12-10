<?php
/**
 * 微服务调用基类
 */
namespace Ets\service;

use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Ets\server\result\JsonResult;
use Ets\service\breaker\Breaker;
use Ets\service\breaker\BreakerInterface;
use Ets\service\client\HttpClient;

abstract class MicroServiceHttpClientBase
{
    /**
     * 默认按host熔断
     *
     * @param HttpClient $httpClient
     * @return mixed
     */
    protected static function getBreakKey(HttpClient $httpClient)
    {
        return $httpClient->getHost();
    }

    /**
     * @param \Throwable $e
     * @param $client
     */
    protected static function fallback(\Throwable $e, HttpClient $client)
    {
        ToolsHelper::throws("调用微服务失败！");
    }

    /**
     * @param HttpClient $httpClient
     * @param $method = post
     * @param string $fallback
     * @param string $breakerComponent
     * @return string
     */
    protected static function callApi(
        HttpClient $httpClient,
        $method = HttpClient::METHOD_POST,
        $fallback = 'static::fallback',
        $breakerComponent = Breaker::class
    ): string {

        /**
         * @var $breaker BreakerInterface
         */
        $breaker = Ets::component($breakerComponent, false);
        $breakKey = static::getBreakKey($httpClient);
        if ($breaker && $breaker->isBreaking($breakKey)) {

            $e = new EtsException("系统熔断中");

            return $fallback($e, $httpClient);
        }

        try {

            return $httpClient->$method();

        } catch (\Throwable $e) {

            if ($breaker && $e instanceof EtsException) {
                $breaker->addError($breakKey);
            }

            return $fallback($e, $httpClient);
        }
    }

    protected static function getData($result)
    {
        return JsonResult::build($result)->checkError()->getData();
    }

}
