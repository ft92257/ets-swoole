<?php

namespace Ets;

use Ets\base\Application;
use Ets\consts\EtsConst;
use Ets\coroutine\CoroutineVar;
use Ets\helper\ToolsHelper;
use Ets\pool\connector\BasePoolConnector;
use Ets\server\HttpServer;


class Ets
{
    /**
     * @var Application
     */
    public static $app;

    const CODE_EXIT_SUCCESS = 200;

    const CODE_EXIT_ERROR = 500;

    // 框架所在路径
    private static $etsPath;

    // 项目路径
    private static $applicationPath;

    protected static function loadConfig($mainConfigFile)
    {
        $mainConfig = require_once($mainConfigFile);
        $defaultConfig = require_once(self::$etsPath . 'server/config/default.php');

        $mainConfig['componentsConfig'] = array_merge(
            $defaultConfig['componentsConfig'], $mainConfig['componentsConfig'] ?? []
        );

        return $mainConfig;
    }

    /**
     * 运行服务
     *
     * @param $applicationPath String 项目路径
     * @param $serverComponent
     * @param $configFile
     * @throws \Exception
     */
    public static function runServer($applicationPath, $serverComponent = HttpServer::class, $configFile = 'common/config/main.php')
    {
        spl_autoload_register('Ets\Ets::autoload', true, true);

        self::$etsPath = dirname(__FILE__) . '/';
        self::$applicationPath = $applicationPath;

        self::$app = new Application(self::loadConfig($applicationPath . $configFile));

        self::$app->runServer($serverComponent);
    }

    public static function autoload($className)
    {
        if (substr($className, 0, 3) == 'Ets') {

            $classFile = self::$etsPath . str_replace('\\', '/', substr($className, 3)) . '.php';

        } else {
            $classFile = dirname(self::$applicationPath) . '/' . str_replace('\\', '/', $className) . '.php';
        }

        if (! is_file($classFile)) {
            throw new \Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
        }

        include $classFile;

        if (! class_exists($className, false) && ! interface_exists($className, false) && ! trait_exists($className, false)) {
            throw new \Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }

    /**
     * @return \Ets\log\Logger
     */
    public static function getLogger()
    {
        return self::$app->logger;
    }

    public static function info($message, $category = 'application')
    {
        self::$app->logger->log($message, 'info', $category);
    }

    public static function error($message, $category = 'application')
    {
        self::$app->logger->log($message, 'error', $category);
    }

    /**
     * 获取组件实例
     *
     * @param $name string 组件名称 或 类名
     * @param $throwException bool 找不到对象时：true抛异常，false返回null
     * @return mixed
     * @throws
     */
    public static function component($name, $throwException = true)
    {
        try {
            if (ToolsHelper::classExists($name)) {
                return self::$app->getComponentByClass($name);
            } else {
                return self::$app->getComponentByName($name);
            }
        } catch (\Throwable $e) {
            if ($throwException) {
                throw $e;
            } else {
                return null;
            }
        }
    }

    /**
     * 获取自定义参数
     *
     * @param string $key 参数名
     * @return mixed
     */
    public static function getParams($key)
    {
        return self::$app->params[$key] ?? null;
    }

    public static function endClear()
    {
        // 释放连接池
        $wrappers = CoroutineVar::getArrayList(EtsConst::COROUTINE_POOL_WRAPPERS);
        /**
         * @var $wrapper BasePoolConnector
         */
        foreach ($wrappers->getValues() as $wrapper) {

            $wrapper->freeUse();
        }

        // 写日志
        Ets::getLogger()->flush();

        // 释放当前协程的自定义全局变量内存
        CoroutineVar::clear();
    }

}

?>
