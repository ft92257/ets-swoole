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
     *
     * @return mixed
     */
    public function getOne(Query $query)
    {
        $db = $this->getDb(true);

        $command = $this->getCommand();

        $row = $command->queryOne($db, $query->buildQuerySql());

        return $query->adaptOneResult($row);
    }

    public function getAll(Query $query)
    {
        $db = $this->getDb(true);

        $command = $this->getCommand();

        $rows = $command->queryAll($db, $query->buildQuerySql());

        return $query->adaptAllResult($rows);
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
        $hashMap = CoroutineVar::getHashMap(EtsConst::COROUTINE_MODEL_OBJECTS);

        $key = static::class . md5(ToolsHelper::toJson($condition));

        $model = $hashMap->get($key);

        if (! $model) {

            $query = $this->createQuery()->where($condition)->resultClass(static::class);

            $model = $this->getOne($query);

            $hashMap->put($key, $model);
        }

        return $model;
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
        $query = $this->createQuery()->where($condition)->resultClass(static::class);

        return $this->getAll($query);
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

    protected function getPrimaryCondition(): array
    {
        $primaryKey = static::getPrimaryKey();
        $where = [];
        if (is_array($primaryKey)) {
            foreach ($primaryKey as $k) {
                $value = $this->{$k};

                if (empty($value)) {
                    throw new EtsException("主键值不能为空：" . $k);
                }

                $where[$k] = $value;
            }
        } else {
            $value = $this->{$primaryKey};
            if (empty($value)) {
                throw new EtsException("主键值不能为空：" . $primaryKey);
            }

            $where = [
                $primaryKey => $value
            ];
        }

        return $where;
    }

    /**
     * @param $attributes
     * @return static
     */
    public function modify(array $attributes)
    {
        $where = $this->getPrimaryCondition();

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