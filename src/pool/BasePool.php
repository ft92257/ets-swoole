<?php
namespace Ets\pool;

use Ets\base\Component;
use Ets\Ets;
use Ets\pool\connector\BasePoolConnector;

abstract class BasePool extends Component
{
    // 父工厂组件
    protected $factoryComponent;

    /**
     * @var BasePoolConnector[]
     */
    protected $connections = [];

    /**
     * @return BasePoolConnector
     */
    public abstract function getConnection();

    public function setFactoryComponent($factoryComponent)
    {
        $this->factoryComponent = $factoryComponent;
    }

    /**
     * 连接或重新连接
     *
     * @return BasePoolConnector
     */
    protected function createConnector()
    {
        /**
         * @var $factory BasePoolConnector
         */
        $factory = Ets::$app->getComponentByName($this->factoryComponent, true);

        return $factory->createConnector();
    }
}