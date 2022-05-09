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
return [
    // 这里的 http 对应默认的 server name，如您需要在其它 server 上使用 Session，需要对应的配置全局中间件
    'http' => [
//        \Hyperf\Session\Middleware\SessionMiddleware::class,
//
//        // 数组内配置您的全局中间件，顺序根据该数组的顺序
//        \Hyperf\Validation\Middleware\ValidationMiddleware::class,

        //跨域
        \App\Utils\Middleware\CorsMiddleware::class
    ],
];
