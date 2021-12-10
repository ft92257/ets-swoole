<?php
namespace Ets\pool;

use Ets\base\EtsException;
use Ets\pool\wrapper\BasePoolWrapper;


class SinglePool extends BasePool
{

    /**
     * 获取连接, 固定单例模式
     * @return BasePoolWrapper
     * @throws \Exception
     */
    public function getConnection()
    {
        $index = 0;
        if (! empty($this->connections[$index]) && $this->connections[$index]->isUsing()) {
            throw new EtsException('连接繁忙，请稍后再试！');
        }

        if (empty($this->connections[$index]) || ! $this->connections[$index]->isConnected()) {
            $this->connections[$index] = $this->getConnectionWrapper();
        }

        // 设置为使用中
        $this->connections[$index]->setUsing();

        return $this->connections[$index];
    }

}