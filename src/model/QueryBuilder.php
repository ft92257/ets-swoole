<?php
namespace Ets\model;

use Ets\base\BaseObject;
use Ets\base\EtsException;

class QueryBuilder extends BaseObject
{

    /**
     * @param string $value
     * @return string
     */
    protected static function addslashesOne($value)
    {
        if ($value !== null) {
            $value = addslashes($value);
        }

        return $value;
    }

    /**
     * @param array $attributes
     */
    protected static function addslashes(& $attributes)
    {
        foreach ($attributes as &$value) {
            if (is_string($value)) {
                $value = self::addslashesOne($value);
            } else if(is_array($value)){
                $value = static::addslashes($value);
            }
        }
    }

    protected static function addSlashesBatch(& $attributes)
    {
        foreach ($attributes as &$value) {
            if (is_string($value)) {
                $value = self::addslashesOne($value);
            } else if(is_array($value)){
                static::addSlashesBatch($value);
            }
        }
    }

    protected static function addslashesCondition(& $condition)
    {
        foreach ($condition as $key => &$value) {
            if (! is_array($value)) {
                // 自定义语句不加转义符
                if (! is_numeric($key)) {
                    $value = self::addslashesOne($value);
                }
                continue;
            }

            if (is_numeric($key)) {
                // 数组
                $value[2] = self::addslashesOne($value[2]);
                if (isset($value[3])) {
                    $value[3] = self::addslashesOne($value[3]);
                }
            } else {
                self::addslashes($value);
            }
        }
    }

    public static function buildInsert(string $tableName, array $attributes)
    {
        self::addslashes($attributes);

        $names = array_keys($attributes);

        $values = [];
        foreach ($attributes as $val) {
            if ($val === null) {
                $values[] = 'NUll';
            } else {
                $values[] = "'$val'";
            }
        }

        $sql = 'INSERT INTO ' . "`".$tableName.'`'
        . ' (`' . join('`, `', $names) . '`)'
        . " VALUES (" . join(', ', $values) . ")";

        return $sql;
    }

    public static function buildBatchInsert(string $tableName,array $fields, array $data, array $createTimeValue)
    {

        if (empty($fields)) {
            throw new EtsException("字段不能为空");
        }

        if (empty($data)) {
            throw new EtsException("数据不能为空");
        }

        self::addSlashesBatch($data);
        $values = [];
        foreach ($data as $line) {
            $value = $createTimeValue;
            foreach ($fields as $field) {
                if (isset($line[$field])) {
                    $value[$field] = "'" . $line[$field] . "'";
                } else {
                    throw new EtsException("字段缺失:{$field}");
                }
            }
            $values[] = "(" . join(',', $value) . ")";
        }


        $sql = 'INSERT INTO ' . "`".$tableName.'`'
            . ' (`' . join('`, `', $fields) . '`)'
            . " VALUES " . join(', ', $values);

        return $sql;
    }

    public static function buildUpdate(string $tableName, array $attributes, $condition)
    {
        self::addslashes($attributes);

        $attr = [];
        foreach ( $attributes as $k => $v) {
            $attr[] = $v === null ? "`$k` = NULL" : "`$k` = '$v'";
        }

        $where = static::buildCondition($condition);

        $sql = "UPDATE `$tableName` SET " . implode( ', ', $attr ) . ($where ? " WHERE $where" : '');

        return $sql;
    }

    public static function buildDelete($tableName, $condition)
    {
        $where = static::buildCondition($condition);

        $sql = 'DELETE FROM `' . $tableName . '`'  . ($where ? " WHERE $where" : '');

        return $sql;
    }

    public static function buildCounters(string $tableName, array $counters, $condition, array $updateTimeValue)
    {
        $attr = [];
        foreach ( $counters as $k => $v) {
            $attr[] = "`$k` = `$k` + $v";
        }

        if (! empty($updateTimeValue)) {
            foreach ($updateTimeValue as $timeField => $value) {
                if (is_int($value)) {
                    $attr[] = "`{$timeField}` =  " . $value;
                } else {
                    $attr[] = "`{$timeField}` =  '" . $value . "'";
                }
            }
        }

        $where = static::buildCondition($condition);

        $sql = "UPDATE `$tableName` SET " . implode( ', ', $attr ) . ($where ? " WHERE $where" : '');

        return $sql;
    }

    public static function buildCondition($condition)
    {

        if (empty($condition)) {
            return '';
        }

        if (is_string($condition)) {
            return $condition;
        }

        if (! is_array($condition)) {
            throw new EtsException('where条件只支持数组和字符串');
        }

        self::addslashesCondition($condition);

        $where = '';
        foreach ($condition as $key => $value) {
            // 没有键名
            if (is_numeric($key)) {
                // 数组
                if (is_array($value)) {
                    $where .= self::buildArrayCondition($value[0], $value[1], $value[2], $value[3] ?? '');
                } else {
                    $where .= ' AND ' . $value;
                }
            } else {
                if (is_array($value)) {
                    $where .= ' AND ' . "`$key`" . ' IN ' . "('". join("','", $value) . "')";
                } else {
                    if ($value === null) {
                        $where .= ' AND ' . "`$key`" . " IS NULL";
                    } else {
                        $where .= ' AND ' . "`$key`" . " = '" . $value . "'";
                    }
                }
            }
        }

        if ($where) {
            $where = substr($where, 5);
        }

        return $where;
    }

    protected static function buildArrayCondition($opt, $field, $value, $extValue)
    {
        $opt = strtoupper($opt);

        switch ($opt){
            case 'BETWEEN':
                return ' AND (`' . $field . '` ' . $opt . " '" . $value . "' AND '". $extValue ."')";
                break;
            case 'IN':
            case 'NOT IN':
                return ' AND (`' . $field . '` ' . $opt . " (" . $value . "))";
                break;
        }

        return ' AND (`' . $field . '` ' . $opt . " '" . $value . "')";
    }
}