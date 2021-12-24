<?php

$config = [

    'componentsConfig' => [
        'defaultErrorHandle' => [
            'class' => \Ets\server\handle\error\DefaultErrorHandler::class,
        ],
        'defaultRequestHandle' => [
            'class' => \Ets\server\handle\request\DefaultRequestHandler::class,
        ],
        'defaultServiceHandle' => [
            'class' => \Ets\server\handle\service\DefaultServiceHandler::class,
        ],
        'defaultConsoleRouter' => [
            'class' => \Ets\server\router\ConsoleRouter::class,
        ],
        'defaultHttpRouter' => [
            'class' => \Ets\server\router\HttpRouter::class,
        ],
        'defaultSerializeRouter' => [
            'class' => \Ets\server\router\SerializeRouter::class
        ],
        'defaultJsonRouter' => [
            'class' => \Ets\server\router\JsonRouter::class,
        ],
        'queueEvent' => [
            'class' => \Ets\queue\BroadcastQueue::class,
        ],
        'command' => [
            'class' => \Ets\model\Command::class
        ],
    ],

    'params' => [],

];

return $config;