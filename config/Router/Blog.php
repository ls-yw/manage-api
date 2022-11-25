<?php
declare(strict_types = 1);

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/blog/',function (){
    Router::addGroup('category/',function (){
        Router::get('list', 'App\Controller\Blog\CategoryController@list');
        Router::post('save', 'App\Controller\Blog\CategoryController@save');
        Router::post('delete', 'App\Controller\Blog\CategoryController@delete');
        Router::post('recovery', 'App\Controller\Blog\CategoryController@recovery');
        Router::get('pairs', 'App\Controller\Blog\CategoryController@pairs');
    });

    Router::addGroup('article/',function (){
        Router::get('list', 'App\Controller\Blog\ArticleController@list');
        Router::post('save', 'App\Controller\Blog\ArticleController@save');
        Router::post('delete', 'App\Controller\Blog\ArticleController@delete');
        Router::post('recovery', 'App\Controller\Blog\ArticleController@recovery');
        Router::get('info', 'App\Controller\Blog\ArticleController@info');
    });
});