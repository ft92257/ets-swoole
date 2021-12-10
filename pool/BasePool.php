<?php
namespace Ets\pool;

use Ets\base\Component;
use Ets\Ets;
use Ets\pool\wrapper\BasePoolWrapper;

abstract class BasePool extends Component
{
    // 连接类组件名称
    protected $_wrapperName;

    /**
     * @var BasePoolWrapper[]
     */
    protected $connections = [];

    /**
     * @return BasePoolWrapper
     */
    public abstract function getConnection();

    public function setWrapperName($wrapperName)
    {
        $this->_wrapperName = $wrapperName;
    }

    /**
     * 连接或重新连接
     *
     * @return BasePoolWrapper
     */
    protected function getConnectionWrapper()
    {
        /**
         * @var $wrapper BasePoolWrapper
         */
        $wrapper = Ets::component($this->_wrapperName);

        return $wrapper->build();
    }
}