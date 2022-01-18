<?php

namespace Ets\components\validator;


use Ets\base\Component;
use Ets\helper\ToolsHelper;


class Validator extends Component
{
    protected $data;

    protected $errors = [];

    public static function build(array $data)
    {
        $validator = new Validator();

        $validator->prepare($data);

        return $validator;
    }

    public function prepare(array $data)
    {
        $this->data = $data;
        $this->errors = [];
    }

    public function validate(bool $throw = true)
    {
        if (! empty($this->errors)) {
            if ($throw) {
                ToolsHelper::throws(join(',', $this->errors));
            } else {
                return $this->errors;
            }
        }

        return null;
    }

    protected function getValue(string $field)
    {
        return $this->data[$field] ?? null;
    }

    protected function addError(string $errorMessage, string $default)
    {
        $this->errors[] = $errorMessage ? $errorMessage : $default;
    }

    public function isString(string $field, string $errorMessage = '')
    {
        if (! is_string($this->getValue($field))) {

            $this->addError($errorMessage, $field . '参数必须是字符串类型');
        }
    }

    public function notEmpty(string $field, string $errorMessage = '')
    {
        if (empty($this->getValue($field))) {

            $this->addError($errorMessage, $field . '参数不能为空');
        }
    }

    public function required(string $field, string $errorMessage = '')
    {
        if ($this->getValue($field) == null) {

            $this->addError($errorMessage, $field . '参数必须');
        }
    }

    public function isNumber(string $field, string $errorMessage = '')
    {
        if (! is_numeric($this->getValue($field))) {

            $this->addError($errorMessage, $field . '参数必须是数字');
        }
    }

    public function length(string $field, int $min, int $max, string $errorMessage = '')
    {
        $len = strlen($this->getValue($field));
        if ($len < $min || $len > $max) {

            $this->addError($errorMessage, $field . '参数字符长度不合法');
        }
    }


}