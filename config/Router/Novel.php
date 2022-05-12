<?php
declare(strict_types = 1);

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/novel/',function (){
    Router::addGroup('category/',function (){
        Router::get('list', 'App\Controller\Novel\CategoryController@list');
        Router::post('save', 'App\Controller\Novel\CategoryController@save');
        Router::post('delete', 'App\Controller\Novel\CategoryController@delete');
        Router::get('pairs', 'App\Controller\Novel\CategoryController@pairs');
    });

    Router::addGroup('book/',function (){
        Router::get('list', 'App\Controller\Novel\BookController@list');
        Router::post('save', 'App\Controller\Novel\BookController@save');
        Router::post('delete', 'App\Controller\Novel\BookController@delete');
        Router::get('chapterPairs', 'App\Controller\Novel\BookController@chapterPairs');
        Router::post('changeCollect', 'App\Controller\Novel\BookController@changeCollect');
    });

    Router::addGroup('article/',function (){
        Router::get('list', 'App\Controller\Novel\ArticleController@list');
        Router::post('save', 'App\Controller\Novel\ArticleController@save');
        Router::post('delete', 'App\Controller\Novel\ArticleController@delete');
        Router::get('content', 'App\Controller\Novel\ArticleController@getContent');
    });

    Router::addGroup('collect/',function (){
        Router::get('list', 'App\Controller\Novel\CollectController@list');
        Router::post('save', 'App\Controller\Novel\CollectController@save');
        Router::post('delete', 'App\Controller\Novel\CollectController@delete');
        Router::get('article', 'App\Controller\Novel\CollectController@collectFormArticle');
        Router::post('confirmArticle', 'App\Controller\Novel\CollectController@batchConfirmCollectArticle');
    });
});
