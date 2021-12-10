<?php
namespace Ets\pool;

use Ets\base\EtsException;
use Ets\pool\wrapper\BasePoolWrapper;
use Swoole\Coroutine;


class Pool extends BasePool
{
    // 连接池数量
    protected $count = 20;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['count'];
    }

    protected function beforeConnect()
    {
    }

    /**
     * 快速查找空闲连接
     */
    private function getIndexQuickly()
    {
        $i = Coroutine::getuid() % $this->count;

        return $i;
    }

    /**
     * 获取连接
     * @return BasePoolWrapper
     * @throws \Exception
     */
    public function getConnection()
    {
        $this->beforeConnect();

        $index = $this->getIndexQuickly();
        if (! empty($this->connections[$index]) && $this->connections[$index]->isUsing()) {
            // 被占用，则全量搜索空闲连接
            $isFound = false;
            for ($i=0;$i<$this->count;$i++) {
                if (! isset($this->connections[$i]) || ! $this->connections[$i]->isUsing()) {
                    $index = $i;
                    $isFound = true;
                    break;
                }
            }
            if (! $isFound) {
                // 未找到空闲的连接
                throw new EtsException('连接繁忙，请稍后再试！');
            }
        }

        if (empty($this->connections[$index]) || ! $this->connections[$index]->isConnected()) {
            $this->connections[$index] = $this->getConnectionWrapper();
        }

        // 设置为使用中
        $this->connections[$index]->setUsing();

        return $this->connections[$index];
    }

}