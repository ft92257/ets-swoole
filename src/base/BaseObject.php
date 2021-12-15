<?php
namespace Ets\base;

class BaseObject
{

    /**
     * 可在子类覆盖重写，返回允许初始化写入的字段数组
     *
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return [];
    }

    /**
     * 可在子类覆盖重写，返回允许读的字段数组
     *
     * @Override
     * @return array
     */
    protected function allowReadFields()
    {
        return [];
    }

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->setAttributeByConfig($config);
        }

        $this->init();
    }

    protected function init() {}

    /**
     * 配置getSetterFields或有setter方法的属性或public属性允许初始化时写入
     * 未定义的属性按public设置
     *
     * @param $config
     */
    protected function setAttributeByConfig($config)
    {
        foreach ($config as $field => $value) {
            if (in_array($field, $this->allowInitFields())) {
                $this->$field = $value;
            } else {
                $setter = 'set' . ucfirst($field);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                } else {
                    $this->setPublicField($this, $field, $value);
                }
            }
        }
    }

    /**
     * 设置public属性
     *
     * @param $obj
     * @param $field
     * @param $value
     */
    protected function setPublicField($obj, $field, $value)
    {
        $obj->$field = $value;
    }

    public function __get($field)
    {
        $allowFields = $this->allowReadFields();

        if (isset($allowFields[$field])) {

            return $this->$field;
        }

        throw new EtsException('Getting unknown property: ' . get_class($this) . '::' . $field);
    }


    /**
     * 对象转数组
     *
     * @return array
     */
    public function toArray()
    {
        $ret = [];
        foreach ($this->allowReadFields() as $field) {
            $ret[$field] = $this->$field;
        }

        $public = get_object_vars($this);

        return array_merge($public, $ret);
    }


}