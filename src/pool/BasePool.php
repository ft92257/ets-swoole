<?php
namespace Ets\pool;

use Ets\base\Component;
use Ets\Ets;
use Ets\pool\connector\BasePoolConnector;

abstract class BasePool extends Component
{
    // 连接类组件名称
    protected $_wrapperName;

    /**
     * @var BasePoolConnector[]
     */
    protected $connections = [];

    /**
     * @return BasePoolConnector
     */
    public abstract function getConnection();

    public function setWrapperName($wrapperName)
    {
        $this->_wrapperName = $wrapperName;
    }

    /**
     * 连接或重新连接
     *
     * @return BasePoolConnector
     */
    protected function getConnectionWrapper()
    {
        /**
         * @var $wrapper BasePoolConnector
         */
        $wrapper = Ets::component($this->_wrapperName);

        return $wrapper->build();
    }
}