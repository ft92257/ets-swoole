<?php
namespace Ets\model;

use Ets\consts\EtsConst;
use Ets\helper\ToolsHelper;
use Ets\base\BaseArrayObject;
use Ets\Ets;
use Ets\base\EtsException;
use Ets\coroutine\CoroutineVar;
use Ets\pool\wrapper\MysqlWrapper;

abstract class Model extends BaseArrayObject
{
    // 创建时间字段
    protected static $createdAt = 'created_at';
    // 修改时间字段
    protected static $updatedAt = 'updated_at';

    public static $command = Command::class;

    // getter配置  属性名 -> 方法名
    protected $getters = ['attributes' => 'getAttributes'];

    private $getterValues = [];

    protected static $dbComponent = MysqlWrapper::class;

    /**
     * @return MysqlWrapper
     */
    public static function getDb()
    {
        return Ets::component(static::$dbComponent);
    }

    abstract protected static function getPrimaryKey();


    /**
     * 从库配置
     *
     * @return array
     */
    public static function getSlaveConfig()
    {
        $slaves = [
            'db' => 30, //30%
            'dbSlave' => 100, //70%
        ];

        // return $slaves;

        return [];
    }

    /**
     * @return mixed
     */
    protected static function getSlaveDb()
    {
        $slaves = static::getSlaveConfig();
        if (empty($slaves)) {
            return static::getDb();
        }

        $random = mt_rand(1, 100);
        $dbName = '';
        foreach ($slaves as $db => $rate) {
            if ($random <= $rate) {
                $dbName = $db;
                break;
            }
        }
        if (empty($dbName)) {
            $dbName = array_keys($slaves)[0];
        }

        return Ets::component($dbName);
    }

    /**
     * 返回表名
     * @return string
     */
    public abstract static function tableName();

    public static function checkPrimaryKey($condition)
    {

    }

    /**
     * @param $slave
     * @return Command
     */
    public static function createCommand($slave = false)
    {
        $command = static::$command;

        $db = $slave ? self::getSlaveDb() : static::getDb();

        return new $command(['db' => $db, 'modelClass' => static::class]);
    }

    /**
     * @return Query
     */
    protected static function find()
    {
        $command = static::createCommand(true);

        $query = new Query([
            'command' => $command,
            'tableName' => static::tableName(),
            'modelClass' => static::class,
        ]);

        return $query;
    }

    public function __get($name)
    {
        $getters = array_merge(['attributes' => 'getAttributes'], $this->getters);
        if (isset($getters[$name])) {
            $getter = $getters[$name];

            if (! isset($this->getterValues[$name])) {
                $this->getterValues[$name] = $this->$getter();
            }

            return $this->getterValues[$name];
        }

        throw new EtsException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    public function getAttributes()
    {
        $ret = get_object_vars($this);
        unset($ret['getters']);
        unset($ret['getterValues']);

        return $ret;
    }

    /**
     * 获取当前对象的单例
     * @param $condition
     * @return mixed
     */
    protected static function _getOne($condition)
    {
        $objects = CoroutineVar::getArrayList(EtsConst::COROUTINE_MODEL_OBJECTS);

        $key = md5(ToolsHelper::toJson($condition));
        if (! isset($objects[static::class][$key])) {
            $data = static::find()->where($condition)->asArray()->one();
            if (empty($data)) {
                return null;
            } else {
                $ret = new static($data);
            }

            $oldData = isset($objects[static::class]) && is_array($objects[static::class]) ? $objects[static::class] : [];
            $objects[static::class] = array_merge($oldData,  [
                $key => $ret
            ]);
        }

        return $objects[static::class][$key];
    }

    /**
     * @param $class static
     * @param $keyMap
     * @return static
     */
    protected function hasOne($class, $keyMap)
    {
        $where = [];
        foreach ($keyMap as $key => $value) {
            $where[$key] = $this->$value;
        }

        return $class::findOne($where);
    }

    /**
     * @param $condition
     * @return static
     * @throws EtsException
     */
    protected static function findOne($condition)
    {
        if (! is_array($condition)) {
            throw new EtsException('查询条件必须是数组！');
        }

        return self::_getOne($condition);
    }

    public static function findAll($condition)
    {
        $rows = static::find()->where($condition)->asArray()->all();
        $ret = [];
        foreach ($rows as $row) {
            $ret[] = new static($row);
        }

        return $ret;
    }

    /**
     * @param $attributes
     * @return static
     */
    public static function create($attributes)
    {
        if (static::$createdAt && ! array_key_exists(static::$createdAt, $attributes)) {
            $attributes[static::$createdAt] = date('Y-m-d H:i:s');
        }
        if (static::$updatedAt && ! array_key_exists(static::$updatedAt, $attributes)) {
            $attributes[static::$updatedAt] = date('Y-m-d H:i:s');
        }

        $sql = QueryBuilder::buildInsert(static::tableName(), $attributes);

        $command = static::createCommand();
        $ret = $command->setSql($sql)->execute();
        $attributes['id'] = $command->getLastInsertId();;

        return new static($attributes);
    }

    protected static function _modify($attributes, $where, $obj)
    {
        $ret = static::updateAll($attributes, $where);
        if ($ret) {
            self::setObjAttributes($obj, $attributes);
        }

        return $obj;
    }

    protected static function setObjAttributes($obj, $config)
    {
        foreach ($config as $field => $value) {
            $obj->$field = $value;
        }
    }

    /**
     * @param $attributes
     * @return static
     */
    protected function modify($attributes)
    {
        $primaryKey = static::getPrimaryKey();
        $where = [];
        if (is_array($primaryKey)) {
            foreach ($primaryKey as $k) {
                $where[$k] = $this->{$k};
            }
        } else {
            $where = [$primaryKey => $this->{$primaryKey}];
        }

        return self::_modify($attributes, $where, $this);
    }

    protected static function updateAll($attributes, $condition)
    {
        static::checkPrimaryKey($condition);

        if (static::$updatedAt && ! isset($attributes[static::$updatedAt])) {
            $attributes[static::$updatedAt] = date('Y-m-d H:i:s');
        }

        $sql = QueryBuilder::buildUpdate(static::tableName(), $attributes, $condition);

        return static::createCommand()->setSql($sql)->execute();
    }

    protected static function deleteAll($condition)
    {
        static::checkPrimaryKey($condition);

        $sql = QueryBuilder::buildDelete(static::tableName(), $condition);

        return static::createCommand()->setSql($sql)->execute();
    }

    protected static function updateAllCounters($counters, $condition)
    {
        static::checkPrimaryKey($condition);

        $sql = QueryBuilder::buildCounters(static::tableName(), $counters, $condition, static::$updatedAt);

        return static::createCommand()->setSql($sql)->execute();
    }

    public static function batchInsert($fields, $data)
    {
        $sql = QueryBuilder::buildBatchInsert(static::tableName(), $fields, $data);

        return static::createCommand()->setSql($sql)->execute();
    }
}