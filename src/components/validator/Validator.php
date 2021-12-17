<?php

namespace Ets\components\lock;


use Ets\base\Component;
use Ets\helper\ToolsHelper;


class Validator extends Component
{
    protected $data;

    protected $errors = [];

    public function prepare($data)
    {
        $this->data = $data;
        $this->errors = [];
    }

    public function isString($field, $errorMessage = '')
    {
        if (! is_string($this->data[$field])) {
            $this->errors[] = $errorMessage ? $errorMessage : $field . '参数必须是字符串类型';
        }
    }

    public function validate($throw = true)
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
}