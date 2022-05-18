<?php
declare(strict_types = 1);

namespace App\Utils\Helper;

class HelperTime
{
    /**
     * 获取时间格式
     *
     * @author yls
     * @param string|null $format
     * @param string|null $time
     * @return string
     */
    public static function now(string $format = null, string $time = null) : string
    {
        if (null === $format) {
            $format = 'Y-m-d H:i:s';
        }
        if (null === $time) {
            $time = time();
        }
        return date($format, $time);
    }
}