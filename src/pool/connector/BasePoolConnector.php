<?php
namespace Ets\pool\connector;

use Ets\base\Component;
use Ets\base\EtsException;
use Ets\consts\EtsConst;
use Ets\Ets;
use Ets\coroutine\CoroutineVar;
use Ets\pool\Pool;
use Ets\pool\SinglePool;

abstract class BasePoolConnector extends Component
{
    /**
     * 连接池配置
     * @setter
     */
    protected $poolConfig = [
        'class' => Pool::class,
    ];

    // 锁时间，毫秒
    protected $lockTime = 5;

    // 装饰对象实例
    /**
     * @var \Object
     */
    protected $instance;

    /**
     * @var Pool $pool
     */
    protected $pool;

    // 锁有效期
    protected $expire;

    // 协程id
    protected $cid;

    public function setPoolConfig($poolConfig)
    {
        $this->poolConfig = $poolConfig;
    }

    public function setLockTime($lockTime)
    {
        $this->lockTime = $lockTime;
    }

    /**
     * 初始化连接池
     */
    public function initPool()
    {
        if (empty($this->poolConfig)) {
            $this->pool = new SinglePool();
        } else {
            $this->pool = Ets::$app->loadComponentInstanceByConfig($this->poolConfig, $this->getComponentName() . '-pool');
        }

        $this->pool->setFactoryComponent($this->getComponentName());
    }

    public function getConnection()
    {
        if (empty($this->pool)) {
            throw new EtsException("请先设置连接池配置");
        }

        return $this->pool->getConnection();
    }

    public function isConnected()
    {
        return $this->instance->connected;
    }

    public function isUsing()
    {
        $cid = CoroutineVar::getCid();

        if (! empty($this->expire) && ! empty($this->cid) && $this->cid != $cid && $this->expire >= microtime(true) * 1000) {
            // 未到期，正在使用
            return true;
        }

        return false;
    }

    public function setUsing()
    {
        $cid = CoroutineVar::getCid();

        $this->expire = microtime(true) * 1000 + $this->lockTime;

        $this->cid = $cid;

        // 添加到待释放资源池
        $wrappers = CoroutineVar::getArrayList(EtsConst::COROUTINE_POOL_WRAPPERS);
        $wrappers->add($this);
    }

    public function freeUse()
    {
        $this->cid = null;
        $this->expire = null;
    }

    /**
     * 实际连接方法
     * @return Object
     */
    protected abstract function connect();

    /**
     * 连接关闭方法
     * @return mixed
     */
    public abstract function close();

    /**
     * 新构建连接实例
     *
     * @return static
     */
    public function createConnector()
    {
        $params = $this->toArray();
        $className = get_class($this);

        /**
         * @var $connector static
         */
        $connector = new $className($params);

        $connector->initConnect();

        return $connector;
    }

    public function initConnect()
    {
        $this->instance = $this->connect();
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_array([$this->instance, $name], $arguments);
    }

    function __get($name)
    {
        return $this->instance->$name;
    }

}