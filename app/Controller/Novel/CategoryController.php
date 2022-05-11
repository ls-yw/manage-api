<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Novel\CategoryService;
use App\Utils\Helper\HelperArray;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CategoryController extends BaseController
{

    /**
     * 分类列表
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function list(RequestInterface $request) : ResponseInterface
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        $data          = [];
        $data['list']  = (new CategoryService())->getList($page, $size);
        $data['total'] = (new CategoryService())->getListCount();

        return $this->success($data);
    }

    /**
     * 保存分类
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function save(RequestInterface $request) : ResponseInterface
    {
        $data = [
            'id'          => (int) $request->input('id'),
            'name'        => (string) $request->input('name'),
            'seo_name'    => (string) $request->input('seo_name'),
            'keyword'     => (string) $request->input('keyword'),
            'description' => (string) $request->input('description'),
            'sort'        => (int) $request->input('sort'),
        ];

        if (empty($data['name'])) {
            throw new ManageException(ErrorCode::EMPTY_NAME);
        }

        $row = (new CategoryService())->saveCategory($data);
        if (!$row) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }
        return $this->success();
    }

    /**
     * 删除分类
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function delete(RequestInterface $request) : ResponseInterface
    {
        $id = (int)$request->input('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new CategoryService())->deleteCategory($id);
        if (!$row) {
            throw new ManageException(ErrorCode::DELETE_FAILED);
        }
        return $this->success();
    }

    /**
     * 分类数组
     *
     * @author yls
     * @return ResponseInterface
     */
    public function pairs() : ResponseInterface
    {
        $data  = (new CategoryService())->getAll(['id', 'name']);

        return $this->success(['data' => HelperArray::showPair($data, 'id', 'name')]);
    }
}