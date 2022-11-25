<?php
declare(strict_types = 1);

namespace App\Services\Blog;

use App\Base\BaseService;
use App\Model\Blog\Category;

class CategoryService extends BaseService
{
    /**
     * 获取列表
     *
     * @author yls
     * @return object
     */
    public function getList() : object
    {
        return Category::orderBy('pid')->get();
    }

    /**
     * 全部
     *
     * @author yls
     * @param int|null $pid
     * @param array    $fields
     * @return object
     */
    public function getAll(int $pid = null, array $fields = ['*']) : object
    {
        $model = Category::select($fields);
        if (null !== $pid) {
            $model->where('pid', $pid);
        }
        return $model->get();
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
}