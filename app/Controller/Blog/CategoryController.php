<?php
declare(strict_types = 1);

namespace App\Controller\Blog;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Blog\CategoryService;
use App\Utils\Helper\HelperArray;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CategoryController extends BaseController
{
    public function list() : ResponseInterface
    {
        $data         = [];
        $data['data'] = (new CategoryService())->getList();
        return $this->success($data);
    }

    public function pairs(RequestInterface $request) : ResponseInterface
    {
        $pid = $request->query('pid');
        if (null !== $pid) {
            $pid = (int)$pid;
        }
        $data = (new CategoryService())->getAll($pid);
        return $this->success(['data' => HelperArray::showPair($data, 'id', 'name')]);
    }

    public function save(RequestInterface $request) : ResponseInterface
    {
        $data = [
            'id'         => (int) $request->input('id'),
            'name'       => $request->input('name'),
            'pid'        => (int) $request->input('pid'),
            'is_deleted' => (int) $request->input('is_deleted'),
        ];
        if (empty($data['name'])) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new CategoryService())->saveCategory($data);
        if (empty($row)) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }
        return $this->success();
    }

    public function delete(RequestInterface $request) : ResponseInterface
    {
        $id = (int)$request->input('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new CategoryService())->saveCategory(['id' => $id, 'is_deleted' => 1]);
        if (empty($row)) {
            throw new ManageException(ErrorCode::DELETE_FAILED);
        }
        return $this->success();
    }

    public function recovery(RequestInterface $request) : ResponseInterface
    {
        $id = (int)$request->input('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new CategoryService())->saveCategory(['id' => $id, 'is_deleted' => 0]);
        if (empty($row)) {
            throw new ManageException(ErrorCode::RECOVERY_FAILED);
        }
        return $this->success();
    }
}