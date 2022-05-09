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
}