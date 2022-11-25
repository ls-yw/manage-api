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
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::post('/upload', 'App\Controller\Admin\UploadController@index');

require_once 'Router/Admin.php';
require_once 'Router/Novel.php';
require_once 'Router/Blog.php';

Router::addServer('ws', function () {
    Router::get('/collect', 'App\Controller\Novel\CollectWebSocketController');
});

Router::get('/favicon.ico', function () {
    return '';
});
