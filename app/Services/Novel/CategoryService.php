<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Model\Novel\Category;

class CategoryService extends BaseService
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
        return Category::orderBy('id', 'desc')->offset($offset)->limit($size)->get();
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @return int
     */
    public function getListCount() : int
    {
        return Category::count();
    }

    /**
     * 全部
     *
     * @author yls
     * @param array $fields
     * @return object
     */
    public function getAll(array $fields = ['*']) : object
    {
        return Category::all($fields);
    }

    /**
     * 保存分类
     *
     * @author yls
     * @param array $data
     * @return int
     */
    public function saveCategory(array $data) : int
    {
        return $this->saveData($data, Category::make());
    }

    /**
     * 删除分类
     *
     * @author yls
     * @param int $id
     * @return int
     */
    public function deleteCategory(int $id):int
    {
        return Category::where('id', $id)->delete();
    }
}