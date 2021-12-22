<?php

namespace Ets\helper;

use Ets\base\BizException;

class ToolsHelper
{
    public static function throws(string $message, $code = -1)
    {
        throw new BizException($message, $code);
    }

    public static function toJson($source)
    {
        return json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 数组转换成xml
     *
     * @param $arr
     * @param string $rootTag
     *
     * @return string
     */
    public static function arrayToXml(array $arr, string $rootTag = '')
    {
        $xml = $rootTag ? '<' . $rootTag . '>' : '';

        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                if (is_numeric($key)) {
                    $xml .= self::arrayToXml($val, '');
                } else {
                    $xml .= '<' . $key . '>' . self::arrayToXml($val, '') . '</' . $key . '>';
                }
            }
            else {
                if (is_numeric($val) || $val === '') {
                    $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
                }
                else {
                    $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
                }
            }
        }

        $xml .= ($rootTag ? '</' . $rootTag . '>' : '');

        return $xml;
    }

    /**
     * 百分比几率计算
     *
     * @param int $percent 百分比， 0 -> 100
     * @return bool
     */
    public static function checkPercent(int $percent)
    {
        return mt_rand(1, 100) <= $percent;
    }

    /**
     * 十一位以内非0开头的数字字符串转为int类型
     *
     * @param $input
     * @return int
     */
    public static function digitizing(string $input)
    {
        if ($input === '0' || (ctype_digit($input) && substr($input, 0, 1) !== '0' && strlen($input) <= 11)) {
            // 数字转整型
            return  intval($input);
        } else {
            // 字符串或其他 原样返回
            return $input;
        }
    }

    public static function digitizingRow(array &$row)
    {
        foreach ($row as &$value) {
            if (is_string($value)) {
                $value = ToolsHelper::digitizing($value);
            }
        }
    }

    /**
     * 检测类是否存在，先自动加载类，找不到时返回false
     *
     * @param string $className
     * @return bool
     */
    public static function classExists(string $className): bool
    {
        try {

            return class_exists($className);

        } catch (\Throwable $e) {

            return false;
        }
    }


}