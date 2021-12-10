<?php

namespace Ets\helper;


class RouterHelper
{
    /*生成controller的路径*/
    public static function buildController(array $arr)
    {
        $last = count($arr) - 1;
        $control = empty($arr[$last]) ? 'index' : $arr[$last];
        $arr[$last] = self::line2camel($control);

        return implode('\\', $arr) . 'Controller';
    }

    // 横线转驼峰
    public static function line2camel($string)
    {
        $pieces = explode('-', $string);
        foreach ($pieces as $key => $value) {
            $pieces[$key] = ucfirst($value);
        }

        return implode('', $pieces);
    }
}

?>