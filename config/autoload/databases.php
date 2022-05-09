<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
$envDatabases = explode(',', env('DATABASES'));

$databases = [];
if (!empty($envDatabases)) {
    foreach ($envDatabases as $value) {
        $databases[strtolower($value)] = [
            'driver' => env(strtoupper($value).'.DRIVER'),
            'host' => env(strtoupper($value).'.HOST'),
            'port' => env(strtoupper($value).'.PORT'),
            'database' => env(strtoupper($value).'.DATABASE'),
            'username' => env(strtoupper($value).'.USERNAME'),
            'password' => env(strtoupper($value).'.PASSWORD'),
            'charset' => env(strtoupper($value).'.CHARSET'),
            'collation' => env(strtoupper($value).'.COLLATION'),
            'prefix' => env(strtoupper($value).'.PREFIX'),
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
            ],
            //        'cache' => [
            //            'handler' => Hyperf\ModelCache\Handler\RedisHandler::class,
            //            'cache_key' => '{mc:%s:m:%s}:%s:%s',
            //            'prefix' => 'default',
            //            'ttl' => 3600 * 24,
            //            'empty_model_ttl' => 600,
            //            'load_script' => true,
            //        ],
            'commands' => [
                'gen:model' => [
                    'path' => 'app/Model/'.ucfirst(strtolower($value)),
                    'force_casts' => true,
                    'inheritance' => 'Model',
                    'refresh_fillable' => true,
                    'uses' => 'App\Model\Model',
                    'with-comments' => true, // 是否增加字段注释
                    'table_mapping' => [],
                ],
            ],
        ];
    }
}
return $databases;
