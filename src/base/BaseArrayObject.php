<?php
namespace Ets\base;

class BaseArrayObject extends BaseObject implements \ArrayAccess
{
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

}