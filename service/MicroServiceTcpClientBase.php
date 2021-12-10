<?php
/**
 * 微服务调用基类
 */
namespace ets\service;

use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Ets\service\breaker\Breaker;
use Ets\service\breaker\BreakerInterface;
use Ets\service\client\TcpClient;

abstract class MicroServiceTcpClientBase
{

    /**
     * @param \Throwable $e
     * @param $client
     */
    protected static function fallback(\Throwable $e, TcpClient $client)
    {
        ToolsHelper::throws("调用微服务失败：" . $client->getUrl());
    }

    protected static function getBreakKey(TcpClient $client)
    {
        return $client->getUrl();
    }

    /**
     * @param TcpClient $tcpClient
     * @param string $fallback
     * @param string $breakerComponent
     * @return string
     */
    protected function callApi(
        TcpClient $tcpClient,
        $fallback = 'static::fallback',
        $breakerComponent = Breaker::class
    ): string {

        /**
         * @var $breaker BreakerInterface
         */
        $breaker = Ets::component($breakerComponent, false);
        $breakKey = static::getBreakKey($tcpClient);

        if ($breaker && $breaker->isBreaking($breakKey)) {

            $e = new EtsException("系统熔断中");

            return $fallback($e, $tcpClient);
        }

        try {

            return $tcpClient->callTcp();

        } catch (\Throwable $e) {

            if ($breaker && $e instanceof EtsException) {
                $breaker->addError($breakKey);
            }

            return $fallback($e, $tcpClient);
        }
    }

}
