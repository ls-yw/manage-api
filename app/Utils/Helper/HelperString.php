<?php
declare(strict_types = 1);

namespace App\Utils\Helper;

class HelperString
{
    /**
     * 生成token
     *
     * @author yls
     * @param string $str
     * @return string
     */
    public static function createToken(string $str) : string
    {
        return base64_encode(crypt(md5($str) . time(), '$5$' . env('APP_NAME', 'app')));
    }

    /**
     * 清除数据bom头
     *
     * @author yls
     * @param $str
     * @return string
     */
    public static function clearBom($str) : string
    {
        $bom = chr(239) . chr(187) . chr(191);
        return str_replace($bom, '', $str);
    }
}