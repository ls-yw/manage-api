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
    'class'       => Monolog\Handler\StreamHandler::class,
    'constructor' => [
        'stream' => BASE_PATH . '/runtime/logs/'.date('Y-m-d').'/info.log',
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
$errorLog = [
    'class'       => Monolog\Handler\StreamHandler::class,
    'constructor' => [
        'stream' => BASE_PATH . '/runtime/logs/'.date('Y-m-d').'/error.log',
        'level'    => Monolog\Logger::ERROR,
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
$waringLog = [
    'class'       => Monolog\Handler\StreamHandler::class,
    'constructor' => [
        'stream' => BASE_PATH . '/runtime/logs/'.date('Y-m-d').'/waring.log',
        'level'    => Monolog\Logger::WARNING,
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
    'class'       => Monolog\Handler\StreamHandler::class,
    'constructor' => [
        'stream' => BASE_PATH . '/runtime/logs/'.date('Y-m-d').'/debug.log',
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

$logConfigs = [$infoLog, $errorLog, $waringLog];
if ('dev' === $appEnv) {
    $logConfigs[] = $bugLog;
}
//$log = array_merge($infoLog, 'dev' === $appEnv ? $bugLog : []);
return [
    'default' => [
        'handlers' => $logConfigs,
    ],
];
