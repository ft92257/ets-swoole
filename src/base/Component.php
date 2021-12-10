<?php
namespace Ets\base;

abstract class Component extends BaseObject
{
    protected $componentName;

    public function getComponentName()
    {
        return $this->componentName;
    }

    public function setComponentName($name)
    {
        $this->componentName = $name;
    }

}