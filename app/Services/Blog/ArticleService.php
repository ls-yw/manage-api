<?php
declare(strict_types = 1);

namespace App\Services\Blog;

use App\Base\BaseService;
use App\Model\Blog\Article;

class ArticleService extends BaseService
{
    /**
     * 获取列表
     *
     * @author yls
     * @param int $page
     * @param int $size
     * @return object
     */
    public function getList(int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        return Article::orderBy('id', 'desc')->offset($offset)->limit($size)->get();
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @return int
     */
    public function getListCount() : int
    {
        return Article::count();
    }

    /**
     * 保存
     *
     * @author yls
     * @param array $data
     * @return int
     */
    public function saveArticle(array $data) : int
    {
        return $this->saveData($data, Article::make());
    }

    public function getInfo(int $id):object
    {
        $info = $this->getById($id);
        return $info;
    }

    public function getById(int $id) : object
    {
        return Article::find($id);
    }
}