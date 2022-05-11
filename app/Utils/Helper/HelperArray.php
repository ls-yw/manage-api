<?php
declare(strict_types = 1);

namespace App\Utils\Helper;

class HelperArray
{
    /**
     * 解析json
     *
     * @author yls
     * @param string $content
     * @param bool   $type
     * @return array|object
     */
    public static function jsonDecode(string $content, bool $type = true) : array|object
    {
        return json_decode($content, $type);
    }

    /**
     * 转为json格式
     *
     * @author yls
     * @param array|object $content
     * @return string
     */
    public static function jsonEncode(array|object $content) : string
    {
        return json_encode($content, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 转化为展示前端的键值对
     *
     * @author yls
     * @param array|object       $data
     * @param string|null $key
     * @param string|null $value
     * @return array
     */
    public static function showPair(array|object $data, string $key = null, string $value = null) : array
    {
        if (empty($data)) {
            return $data;
        }

        $arr = [];
        foreach ($data as $k => $v) {
            if (null === $key || null === $value) {
                $arr[] = ['value' => $k, 'label' => $v];
            } else {
                $arr[] = ['value' => is_object($v) ? $v->{$key} : $v[$key], 'label' =>  is_object($v) ? $v->{$value} : $v[$value]];
            }
        }
        return $arr;
    }
}