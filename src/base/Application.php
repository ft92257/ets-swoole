<?php
namespace Ets\base;

use Ets\Ets;
use Ets\log\Logger;
use Ets\pool\connector\BasePoolConnector;
use Ets\server\BaseServer;
use Swoole\Runtime;

class Application extends BaseObject
{
    const CLASS_FIELD = 'class';

    public $params = [];

    protected $componentsConfig = [];

    private $_components = [];

    protected $componentClassMap = [];

    /**
     * @var $log Logger
     */
    public $logger;

    /**
     * @var BaseServer $server
     */
    protected $server;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return [
            'params', 'componentsConfig'
        ];
    }

    public function getAppName()
    {
        return $this->server->getAppConfigValue('name', '');
    }

    public function getEnv()
    {
        return $this->server->getAppConfigValue('env', 'dev');
    }

    public function isDebug()
    {
        return $this->server->getAppConfigValue('debug', false);
    }

    public function runServer($serverComponent, $loggerComponent = Logger::class)
    {
        Runtime::enableCoroutine();

        $this->initComponentClassMap();

        $this->logger = Ets::component($loggerComponent);

        /**
         * @var $server BaseServer
         */
        $this->server = Ets::component($serverComponent);
        $this->server->run();
    }

    protected function initComponentClassMap()
    {
        foreach ($this->componentsConfig as $name => $componentConfig) {
            if (isset($this->componentClassMap[$componentConfig[self::CLASS_FIELD]])) {
                $this->componentClassMap[$componentConfig[self::CLASS_FIELD]][] = $name;
            } else {
                $this->componentClassMap[$componentConfig[self::CLASS_FIELD]] = [$name];
                $parents = class_parents($componentConfig[self::CLASS_FIELD]);
                $implements = class_implements($componentConfig[self::CLASS_FIELD]);
                $parents = array_merge($parents, $implements);

                foreach ($parents as $parent) {
                    if (isset($this->componentClassMap[$parent])) {
                        $this->componentClassMap[$parent][] = $name;
                    } else {
                        $this->componentClassMap[$parent] = [$name];
                    }
                }
            }
        }
    }

    /**
     * 根据名称获取component组件
     *
     * @param $name
     * @param bool $getConnectorParent 是否获取连接组件的父工厂组件
     * @return mixed
     * @throws EtsException
     */
    public function getComponentByName($name, $getConnectorParent = false)
    {
        if (empty($this->_components[$name])) {
            $this->_components[$name] = $this->loadComponentInstance($name);
        }

        if (! $getConnectorParent && $this->_components[$name] instanceof BasePoolConnector) {
            /**
             * 原始组件，不带连接
             * @var $connector BasePoolConnector
             */
            $connector = $this->_components[$name];

            // 真实带连接的组件
            return $connector->getConnection();
        }

        return $this->_components[$name];
    }

    public function getComponentByClass($className)
    {
        if (empty($this->componentClassMap[$className])) {
            throw new EtsException("component未配置：" . $className);
        }

        $names = $this->componentClassMap[$className];
        if (count($names) > 1) {
            throw new EtsException("找到多个component，请指定名称：" . $className);
        }
        return $this->getComponentByName($names[0]);
    }

    /**
     * 加载组件实例
     *
     * @param $name
     * @return mixed
     * @throws EtsException
     */
    public function loadComponentInstance($name)
    {
        if (empty($this->componentsConfig[$name])) {
            throw new EtsException('component未配置：' . $name);
        }

        $component = $this->componentsConfig[$name];

        return $this->loadComponentInstanceByConfig($component, $name);
    }

    public function loadComponentInstanceByConfig($component, $name)
    {
        $class = $component[self::CLASS_FIELD];
        unset($component[self::CLASS_FIELD]);

        /**
         * @var $instance Component
         */
        $instance = new $class($component);
        if (! ($instance instanceof Component)) {
            throw new EtsException('component需继承Component基类：' . $name);
        }

        $instance->setComponentName($name);

        if ($instance instanceof BasePoolConnector) {
            // 连接池实体类, 设置连接池对象
            $instance->initPool();
        }

        return $instance;
    }

    /**
     * @param $component array 组件配置
     * @param $name
     * @return mixed
     */
    public function getComponentByConfig($component, $name)
    {
        if (empty($this->_components[$name])) {
            $this->_components[$name] = $this->loadComponentInstanceByConfig($component, $name);
        }

        return $this->_components[$name];
    }

    /**
     * 获取实现了某个基类/接口的所有组件
     *
     * @param $superClass
     * @return array
     */
    public function getComponentsBySuperClass($superClass)
    {
        $components = [];
        foreach ($this->componentClassMap[$superClass] as $componentName) {

            $components[] = $this->getComponentByName($componentName);
        }

        return $components;
    }

}