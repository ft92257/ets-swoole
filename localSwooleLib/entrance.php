<?php
/**
 * 仅限于命令行模式本地调试
 */

function go($func, $params = null)
{
    $func();
}

define("SWOOLE_SOCK_TCP", 1);

require_once 'Coroutine.php';
require_once 'Coroutine/Mysql.php';
require_once 'Coroutine/Redis.php';
require_once 'Coroutine/Http/Client.php';
