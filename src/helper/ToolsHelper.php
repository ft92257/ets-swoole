<?php

namespace Ets\helper;

use Ets\base\BizException;

class ToolsHelper
{
    public static function throws($message, $code = -1)
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
    public static function arrayToXml($arr, $rootTag = '')
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


}