<?php
namespace Ets\coroutine;

use Ets\base\BaseArrayObject;

class CoroutineArrayList extends BaseArrayObject
{
    private $index = 0;

    /**
     * 增加数组元素
     * @param $value
     */
    public function add($value)
    {
        $key = 'index_' . $this->index;
        $this->$key = $value;
        $this->index++;
    }

    /**
     * 获取数组值
     * @return array
     */
    public function getValues()
    {
        $ret = [];
        for ($i=0;$i<$this->index;$i++) {
            $key = 'index_' . $i;
            $ret[] = $this->$key;
        }

        return $ret;
    }

    /**
     * 设置数组元素
     * @param $values
     */
    public function setValues($values)
    {
        if (empty($value) || ! is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * 获取数组元素数量
     * @return int
     */
    public function count()
    {
        return $this->index;
    }

}