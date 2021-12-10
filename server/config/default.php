<?php

$config = [

    'componentsConfig' => [
        'defaultErrorHandle' => [
            'class' => \Ets\server\errorHandle\DefaultErrorHandler::class,
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
    ],

    'params' => [],

];

return $config;