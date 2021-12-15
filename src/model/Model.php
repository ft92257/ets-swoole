<?php
namespace Ets\model;

use Ets\consts\EtsConst;
use Ets\helper\ToolsHelper;
use Ets\base\BaseArrayObject;
use Ets\Ets;
use Ets\base\EtsException;
use Ets\coroutine\CoroutineVar;
use Ets\pool\connector\MysqlConnector;

abstract class Model extends BaseArrayObject implements ModelInterface
{
    /**
     * @return static
     */
    public static function build()
    {
        return new static();
    }

    /**
     * @override
     * @return array
     */
    public function getCreateTimeValue(): array
    {
        return [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * @override
     * @return array
     */
    public function getUpdateTimeValue(): array
    {
        return [
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * @override
     * @return string
     */
    public function getDbComponent(): string
    {
        return MysqlConnector::class;
    }

    /**
     * @param $isUseSlave
     * @return MysqlConnector
     */
    protected function getDb(bool $isUseSlave = false)
    {
        $db = $isUseSlave ? self::getSlaveDb() : Ets::component($this->getDbComponent());

        return $db;
    }

    /**
     * @return CommandInterface
     */
    protected function getCommand()
    {
        return Ets::component(Command::class);
    }

    /**
     * 主键值，修改操作时需用到
     *
     * @override
     * @return string|array
     */
    public function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * 从库配置
     *
     * @return array
     */
    public function getSlaveConfig() :array
    {
        $slaves = [
            'db' => 30, //30%
            'dbSlave' => 70, //70%
        ];

        // return $slaves;

        return [];
    }

    /**
     * @return MysqlConnector
     */
    protected function getSlaveDb()
    {
        $slaves = $this->getSlaveConfig();
        if (empty($slaves)) {
            return $this->getDb();
        }

        $random = mt_rand(1, 100);
        $dbName = '';
        $rateNum = 0;
        foreach ($slaves as $db => $rate) {
            $rateNum += $rate;

            if ($random <= $rateNum) {
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
     * 查询单条记录
     *
     * @param Query $query
     * @param $returnClass
     *
     * @return mixed
     */
    public function getOne(Query $query, string $returnClass = BaseArrayObject::class)
    {
        $db = $this->getDb(true);

        $command = $this->getCommand();

        $row = $command->queryOne($db, $query->buildQuerySql());

        if (empty($returnClass)) {
            return $row;
        }

        if (empty($row)) {
            return null;
        }

        return new $returnClass($row);
    }

    public function getAll(Query $query, string $returnClass = BaseArrayObject::class)
    {
        $db = $this->getDb(true);

        $command = $this->getCommand();

        $rows = $command->queryAll($db, $query->buildQuerySql());

        if (empty($returnClass)) {
            return $rows;
        }

        if (empty($rows)) {
            return null;
        }

        $objects = [];
        foreach ($rows as $row) {
            $objects[] = new $returnClass($row);
        }

        return $objects;
    }

    /**
     * @return Query
     */
    protected function createQuery()
    {
        $query = new Query([
            'tableName' => $this->getTableName(),
        ]);

        return $query;
    }

    /**
     * 获取当前对象的单例
     * @param $condition
     * @return mixed
     */
    protected function getOneByCondition($condition)
    {
        $objects = CoroutineVar::getArrayList(EtsConst::COROUTINE_MODEL_OBJECTS);

        $key = md5(ToolsHelper::toJson($condition));

        if (! isset($objects[static::class][$key])) {

            $query = $this->createQuery()->where($condition);

            $model = $this->getOne($query, static::class);

            $oldData = isset($objects[static::class]) && is_array($objects[static::class]) ? $objects[static::class] : [];
            $objects[static::class] = array_merge($oldData,  [
                $key => $model
            ]);
        }

        return $objects[static::class][$key];
    }

    /**
     * @param $class static
     * @param $keyMap
     * @return static
     */
    protected function hasOne($class, array $keyMap)
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
    public function findOne($condition)
    {
        if (! is_array($condition)) {
            throw new EtsException('查询条件必须是数组！');
        }

        return self::getOneByCondition($condition);
    }

    public function findAll($condition)
    {
        $query = $this->createQuery()->where($condition);

        return $this->getAll($query, static::class);
    }

    /**
     * @param $attributes
     * @param $id
     * @return static
     */
    public function create(array $attributes, string $id = 'id')
    {
        $attributes = array_merge($this->getCreateTimeValue(), $attributes);

        $sql = $this->createQuery()->buildInsertSql($attributes);

        $this->getCommand()->execute($this->getDb(), $sql);

        $attributes[$id] = $this->getCommand()->getLastInsertId($this->getDb());;

        return new static($attributes);
    }

    protected function getModifyCondition(): array
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

        return $where;
    }

    /**
     * @param $attributes
     * @return static
     */
    public function modify(array $attributes)
    {
        $where = $this->getModifyCondition();

        $ret = static::updateAll($attributes, $where);
        if ($ret) {
            $this->setAttributeByConfig($attributes);
        }

        return $this;
    }

    public function updateAll(array $attributes, $condition)
    {
        $attributes = array_merge($this->getUpdateTimeValue(), $attributes);

        $sql = $this->createQuery()->buildUpdateSql($attributes, $condition);

        return $this->getCommand()->execute($this->getDb(), $sql);
    }

    public function deleteAll($condition)
    {
        $sql = $this->createQuery()->buildDeleteSql($condition);

        return $this->getCommand()->execute($this->getDb(), $sql);
    }

    public function updateAllCounters(array $counters, $condition)
    {
        $sql = $this->createQuery()->buildCounterSql($counters, $condition, $this->getUpdateTimeValue());

        return $this->getCommand()->execute($this->getDb(), $sql);
    }

    public function batchInsert(array $fields, array $data)
    {
        $sql = $this->createQuery()->buildBatchInsertSql($fields, $data, $this->getCreateTimeValue());

        return $this->getCommand()->execute($this->getDb(), $sql);
    }
}