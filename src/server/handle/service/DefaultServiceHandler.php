<?php

namespace Ets\server\handle\service;

use Ets\base\Component;
use Ets\consts\EtsConst;
use Ets\coroutine\CoroutineVar;
use Ets\service\client\HttpClient;
use Ets\service\client\TcpClient;

class DefaultServiceHandler extends Component implements ServiceHandlerInterface
{
    /**
     * @param HttpClient $httpClient
     */
    public function beforeCallHttpMicroService($httpClient)
    {
        $traceId = CoroutineVar::getObject(EtsConst::COROUTINE_TRACE_ID);
        if ($traceId) {
            $httpClient->setHeader(EtsConst::HEADER_TRACE_ID, $traceId);
        }
    }


    /**
     * @param TcpClient $tcpClient
     */
    public function beforeTcpMicroService($tcpClient)
    {

    }
}
