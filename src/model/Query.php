<?php
namespace Ets\model;

use Ets\base\BaseArrayObject;
use Ets\base\BaseObject;
use Ets\base\EtsException;

class Query extends BaseObject
{

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var Model
     */
    public $modelClass;

    public $tableName;

    public $select = '*';

    public $from;

    public $groupBy;

    public $having;

    public $offset = 0;

    public $limit;

    public $where;

    public $orderBy;

    public $asArray = false;

    public $asModel;

    /**
     * @return $this
     */
    public function asArray()
    {
        $this->asArray = true;

        return $this;
    }

    /**
     * @param $modelClass string 模型类名
     * @return $this
     */
    public function asModel($modelClass)
    {
        $this->asModel = $modelClass;

        return $this;
    }


    /**
     * 查询单条记录
     *
     * @return mixed
     */
    public function one()
    {
        $this->limit(1);

        $sql = QueryBuilder::buildQuery($this);

        $ret = $this->command->setSql($sql)->queryOne();

        if ($this->asArray) {
            return $ret;
        }

        if (empty($ret)) {
            return null;
        }

        if ($this->asModel) {
            $class = $this->asModel;
            return new $class($ret);
        }

        return new BaseArrayObject($ret);
    }

    /**
     * 查询多条记录
     *
     * @return mixed
     */
    public function all()
    {
        $sql = QueryBuilder::buildQuery($this);

        $ret = $this->command->setSql($sql)->queryAll();

        if ($this->asArray) {
            return $ret;
        }

        $objs = [];
        $class = $this->asModel ?: 'Ets\\base\\BaseArrayObject';
        foreach ($ret as $row) {
            $objs[] = new $class($row);
        }

        return $objs;
    }

    public function count()
    {        
        $this->select('count('. $this->select . ') as c');
        $ret = $this->asArray()->one();

        return $ret['c'];
    }

    public function sum($select)
    {
        $this->select('sum('. $select . ') as s');
        $ret = $this->asArray()->one();

        return $ret['s'];
    }

    /**
     * @param $condition
     * @return $this
     * @throws EtsException
     */
    public function where($condition)
    {
        if (is_string($condition)) {
            // 纯字符串 'id=1'
            $this->where = [$condition];
        } elseif (is_array($condition)) {
            if (isset($condition[0]) && isset($condition[1])) {
                // >= , <= 等复杂条件 ['>=', 'id', 5]
                $this->where = [$condition];
            } elseif (isset($condition[0]) && ! isset($condition[1])) {
                // 数组但条件是字符串 ['id=1']，不允许混用 ['id=1', 'uid' => 2]
                $this->where = [$condition[0]];
                if (count($condition) > 1) {
                    throw new EtsException('纯字符串条件不能混合使用');
                }
            } else {
                // map型 ['id' => 2, 'uid' => 3]
                $this->where = $condition;
            }
        } else {
            throw new EtsException('不支持该格式的查询条件');
        }

        return $this;
    }

    /**
     * @param $condition
     * @return $this
     * @throws EtsException
     */
    public function andWhere($condition)
    {
        if ($this->where === null) {
            return $this->where($condition);
        }

        if (is_string($condition)) {
            // 纯字符串 'id=1'
            $this->where[] = $condition;
        } elseif (is_array($condition)) {
            if (isset($condition[0]) && isset($condition[1])) {
                // >= , <= 等复杂条件 ['>=', 'id', 5]
                $this->where[] = $condition;
            } elseif (isset($condition[0]) && ! isset($condition[1])) {
                // 数组但条件是字符串 ['id=1'], 不允许混用 ['id=1', 'uid' => 2]
                $this->where[] = $condition[0];
                if (count($condition) > 1) {
                    throw new EtsException('纯字符串条件不能混合使用');
                }
            } else {
                // map型 ['id' => 2]
                $this->where = array_merge($this->where, $condition);
            }
        } else {
            throw new EtsException('不支持该格式的查询条件');
        }

        return $this;
    }

    protected static function makeCondition($condition)
    {
        if (is_string($condition)) {
            return $condition;
        }
        if (! is_array($condition)) {
            throw new EtsException('不支持该格式的查询条件');
        }
        if (isset($condition[0]) && isset($condition[1])) {
            return [$condition];
        }
        if (isset($condition[0]) && ! isset($condition[1])) {
            if (count($condition) > 1) {
                throw new EtsException('纯字符串条件不能混合使用');
            }

            return $condition[0];
        }

        return $condition;
    }

    public function andOrWhere($condition)
    {
        if (! is_array($condition)) {
            throw new EtsException('andOrWhere只支持数组格式');
        }
        if (! isset($condition[0])) {
            throw new EtsException('请使用二维数组');
        }
        if (empty($condition)) {
            return $this;
        }

        $sqls = [];
        foreach ($condition as $row) {

            $sqls[] = QueryBuilder::buildCondition(self::makeCondition($row));
        }

        if (count($sqls) == 1) {
            $whereSql = $sqls[0];
        } else {
            $whereSql =  '((' . join(') OR (', $sqls) . '))';
        }

        $this->andWhere($whereSql);

        return $this;
    }

    /**
     * @param $select
     * @return $this
     */
    public function select($select)
    {
        if (is_array($select)) {
            $this->select = join(',', $select);
        } else {
            $this->select = $select;
        }

        return $this;
    }

    /**
     * @param $from
     * @return $this
     */
    public function from($from)
    {
        $this->tableName = $from;

        return $this;
    }

    /**
     * @param $groupBy
     * @return $this
     */
    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @param $condition
     * @return $this
     */
    public function having($condition)
    {
        $this->having = $condition;

        return $this;
    }

    /**
     * @param $orderBy
     * @return $this
     */
    public function orderBy($orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function getRawSql()
    {
        return QueryBuilder::buildQuery($this);
    }

}