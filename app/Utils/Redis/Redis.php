<?php
declare(strict_types = 1);

namespace App\Utils\Redis;

use Hyperf\Redis\Redis as hyperfRedis;
use Hyperf\Utils\ApplicationContext;

class Redis
{
    private static mixed $_instance = null;

    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance->init();
    }

    public function init()
    {
        $container = ApplicationContext::getContainer();

        return $container->get(hyperfRedis::class);
    }
}