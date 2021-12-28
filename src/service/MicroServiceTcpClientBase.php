<?php
/**
 * 微服务调用基类
 */
namespace Ets\service;

use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Ets\server\handle\service\DefaultServiceHandler;
use Ets\server\handle\service\ServiceHandlerInterface;
use Ets\service\breaker\Breaker;
use Ets\service\breaker\BreakerInterface;
use Ets\service\client\TcpClient;

abstract class MicroServiceTcpClientBase
{

    /**
     * @param \Throwable $e
     * @param $client
     */
    protected function fallback(\Throwable $e, TcpClient $client)
    {
        ToolsHelper::throws("调用微服务失败：" . $client->getUrl());
    }

    protected function getBreakKey(TcpClient $client)
    {
        return $client->getUrl();
    }

    /**
     * 可在子类覆盖，替换为自定义的handle
     *
     * @return ServiceHandlerInterface
     */
    protected function getServiceHandle()
    {
        return Ets::component(DefaultServiceHandler::class, false);
    }

    /**
     * @return BreakerInterface
     */
    protected function getBreaker()
    {
        return Ets::component(Breaker::class, false);
    }

    /**
     * @param TcpClient $tcpClient
     * @return string
     */
    protected function callApi(
        TcpClient $tcpClient
    ): string {

        $breaker = $this->getBreaker();
        $breakKey = static::getBreakKey($tcpClient);

        if ($breaker && $breaker->isBreaking($breakKey)) {

            $e = new EtsException("系统熔断中");

            $this->fallback($e, $tcpClient);
            return '';
        }

        try {

            $serviceHandle = $this->getServiceHandle();
            if ($serviceHandle) {
                $serviceHandle->beforeTcpMicroService($tcpClient);
            }

            return $tcpClient->callTcp();

        } catch (\Throwable $e) {

            if ($breaker && $e instanceof EtsException) {
                $breaker->addError($breakKey);
            }

            $this->fallback($e, $tcpClient);
            return '';
        }
    }

}
