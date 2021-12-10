<?php
namespace Swoole;

class Coroutine
{
    public static function getuid()
    {
        return 1;
    }

    public static function set($params)
    {

    }

    public static function writeFile($logFile, $text, $flags)
    {
        file_put_contents($logFile, $text, $flags);
    }

    public static function sleep($seconds)
    {
        sleep($seconds);
    }
}

class Runtime
{
    public static function enableCoroutine()
    {

    }
}