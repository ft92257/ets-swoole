<?php

namespace Ets\server\handle\request;


use Ets\service\client\HttpClient;
use Ets\service\client\TcpClient;

interface ServiceHandlerInterface
{

    /**
     * @param HttpClient $httpClient
     */
    public function beforeCallHttpMicroService($httpClient);


    /**
     * @param TcpClient $tcpClient
     */
    public function beforeTcpMicroService($tcpClient);

}
