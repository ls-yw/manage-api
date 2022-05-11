<?php
declare(strict_types = 1);

use Hyperf\HttpServer\Router\Router;

Router::post('/login', 'App\Controller\Admin\LoginController@login');
Router::get('/getLoginInfo', 'App\Controller\Admin\LoginController@loginInfo');
Router::post('/logout', 'App\Controller\Admin\LoginController@logout');