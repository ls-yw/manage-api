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
        Router::get('apply', 'App\Controller\Novel\BookController@apply');
        Router::post('replyApply', 'App\Controller\Novel\BookController@replyApply');
        Router::post('deleteApply', 'App\Controller\Novel\BookController@deleteApply');
    });

    Router::addGroup('article/',function (){
        Router::get('list', 'App\Controller\Novel\ArticleController@list');
        Router::post('save', 'App\Controller\Novel\ArticleController@save');
        Router::post('delete', 'App\Controller\Novel\ArticleController@delete');
        Router::get('content', 'App\Controller\Novel\ArticleController@getContent');
    });

    Router::addGroup('collect/',function (){
        Router::get('list', 'App\Controller\Novel\CollectController@list');
        Router::get('info', 'App\Controller\Novel\CollectController@info');
        Router::post('save', 'App\Controller\Novel\CollectController@save');
        Router::post('delete', 'App\Controller\Novel\CollectController@delete');
        Router::get('article', 'App\Controller\Novel\CollectController@collectFormArticle');
        Router::post('confirmArticle', 'App\Controller\Novel\CollectController@batchConfirmCollectArticle');
        Router::post('test', 'App\Controller\Novel\CollectController@test');
        Router::post('collectBookInfo', 'App\Controller\Novel\CollectController@collectBookInfo');
        Router::post('collectSaveBookInfo', 'App\Controller\Novel\CollectController@collectSaveBookInfo');
    });

    Router::addGroup('member/',function (){
        Router::get('list', 'App\Controller\Novel\MemberController@list');
        Router::get('book', 'App\Controller\Novel\MemberController@book');
    });

    Router::addGroup('data/',function (){
        Router::get('search', 'App\Controller\Novel\DataController@search');
        Router::get('spider', 'App\Controller\Novel\DataController@searchSpider');
    });

    Router::addGroup('setting/',function (){
        Router::get('index', 'App\Controller\Novel\SettingController@index');
        Router::post('save', 'App\Controller\Novel\SettingController@save');
    });
});
