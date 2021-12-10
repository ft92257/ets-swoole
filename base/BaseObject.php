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
     * 配置getSetterFields或有setter方法的属性允许初始化时写入
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
                }
            }
        }
    }

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->setAttributeByConfig($config);
        }

        $this->init();
    }

    protected function init() {}

}