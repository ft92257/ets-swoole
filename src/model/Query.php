<?php
namespace Ets\model;

use Ets\base\BaseArrayObject;
use Ets\base\BaseObject;
use Ets\base\EtsException;

class Query extends BaseObject
{
    public $tableName;

    protected $select = '*';

    protected $from;

    protected $groupBy;

    protected $having;

    protected $offset = 0;

    protected $limit;

    protected $where;

    protected $orderBy;

    protected $rawSql;

    protected $resultClass = BaseArrayObject::class;

    public function buildQuerySql()
    {
        $this->rawSql = QueryBuilder::buildQuery($this);

        return $this->rawSql;
    }

    public function buildInsertSql(array $attributes)
    {
        $this->rawSql = QueryBuilder::buildInsert($this->tableName, $attributes);

        return $this->rawSql;
    }

    public function buildUpdateSql(array $attributes, $condition)
    {
        $this->rawSql = QueryBuilder::buildUpdate($this->tableName, $attributes, $condition);

        return $this->rawSql;
    }

    public function buildDeleteSql($condition)
    {
        $this->rawSql = QueryBuilder::buildDelete($this->tableName, $condition);

        return $this->rawSql;
    }

    public function buildCounterSql(array $counters, $condition, array $updateTimeValue)
    {
        $this->rawSql = QueryBuilder::buildCounters($this->tableName, $counters, $condition, $updateTimeValue);

        return $this->rawSql;
    }

    public function buildBatchInsertSql(array $fields, array $data, array $createTimeValue)
    {
        $this->rawSql = QueryBuilder::buildBatchInsert($this->tableName, $fields, $data, $createTimeValue);

        return $this->rawSql;
    }


    public function getRawSql()
    {
        return $this->rawSql;
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

    public function resultClass(string $resultClass)
    {
        $this->resultClass = $resultClass;
    }

    public function adaptOneResult(array $row)
    {
        if (empty($row)) {
            return null;
        }

        if (empty($this->resultClass)) {
            return $row;
        }

        return new $this->resultClass($row);
    }

    public function adaptAllResult(array $rows)
    {
        if (empty($rows)) {
            return null;
        }

        if (empty($this->resultClass)) {
            return $rows;
        }

        $objects = [];
        foreach ($rows as $row) {
            $objects[] = new $this->resultClass($row);
        }

        return $objects;
    }

}