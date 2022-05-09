<?php
declare(strict_types = 1);

use Hyperf\HttpServer\Router\Router;

Router::post('/login.json', 'App\Controller\Admin\LoginController@login');