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
     * ??????????????????component??????
     *
     * @param $name
     * @param bool $getConnectorParent ??????????????????????????????????????????
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
             * ???????????????????????????
             * @var $connector BasePoolConnector
             */
            $connector = $this->_components[$name];

            // ????????????????????????
            return $connector->getConnection();
        }

        return $this->_components[$name];
    }

    public function getComponentByClass($className)
    {
        if (empty($this->componentClassMap[$className])) {
            throw new EtsException("component????????????" . $className);
        }

        $names = $this->componentClassMap[$className];
        if (count($names) > 1) {
            throw new EtsException("????????????component?????????????????????" . $className);
        }
        return $this->getComponentByName($names[0]);
    }

    /**
     * ??????????????????
     *
     * @param $name
     * @return mixed
     * @throws EtsException
     */
    public function loadComponentInstance($name)
    {
        if (empty($this->componentsConfig[$name])) {
            throw new EtsException('component????????????' . $name);
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
            throw new EtsException('component?????????Component?????????' . $name);
        }

        $instance->setComponentName($name);

        if ($instance instanceof BasePoolConnector) {
            // ??????????????????, ?????????????????????
            $instance->initPool();
        }

        return $instance;
    }

    /**
     * @param $component array ????????????
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
     * ???????????????????????????/?????????????????????
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