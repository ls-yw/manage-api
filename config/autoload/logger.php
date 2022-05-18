<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
$appEnv = env('APP_ENV', 'dev');

$infoLog = [
    'class'       => Monolog\Handler\RotatingFileHandler::class,
    'constructor' => [
        'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
        'level'    => Monolog\Logger::INFO,
    ],
    'formatter'   => [
        'class'       => Monolog\Formatter\LineFormatter::class,
        'constructor' => [
            'format'                => null,
            'dateFormat'            => 'Y-m-d H:i:s',
            'allowInlineLineBreaks' => true,
        ],
    ],
];
$bugLog  = [
    'class'       => Monolog\Handler\RotatingFileHandler::class,
    'constructor' => [
        'filename' => BASE_PATH . '/runtime/logs/hyperf-debug.log',
        'level'    => Monolog\Logger::DEBUG,
    ],
    'formatter'   => [
        'class'       => Monolog\Formatter\LineFormatter::class,
        'constructor' => [
            'format'                => null,
            'dateFormat'            => 'Y-m-d H:i:s',
            'allowInlineLineBreaks' => true,
        ],
    ]
];

$logConfigs = [$infoLog];
if ('dev' === $appEnv) {
    $logConfigs[] = $bugLog;
}
//$log = array_merge($infoLog, 'dev' === $appEnv ? $bugLog : []);
return [
    'default' => [
        /*'handler'   => [
            'class'       => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level'  => Monolog\Logger::INFO,
            ],
        ],*/
        /*'handler'   => [
            'class'       => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level'    => Monolog\Logger::INFO,
            ],
        ],
        'formatter' => [
            'class'       => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format'                => null,
                'dateFormat'            => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],*/
        'handlers' => [
            $logConfigs
        ],
    ],
];
