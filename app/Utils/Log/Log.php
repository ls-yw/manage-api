<?php
declare(strict_types = 1);

namespace App\Utils\Log;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;

class Log
{
    public static function get(string $name = 'app') : \Psr\Log\LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }

    public static function info(string $message, string $mark = 'app')
    {
        $log = self::get($mark);
        $log->info($message);
    }

    public static function error(string $message, string $mark = 'app')
    {
        $log = self::get($mark);
        $log->error($message);
    }

    public static function waring(string $message, string $mark = 'app')
    {
        $log = self::get($mark);
        $log->warning($message);
    }
}