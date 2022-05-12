<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Novel\CollectService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CollectController extends BaseController
{
    /**
     * 列表
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
        $data['list']  = (new CollectService())->getList($page, $size);
        $data['total'] = (new CollectService())->getListCount();

        return $this->success($data);
    }

    public function save(RequestInterface $request) : ResponseInterface
    {

    }

    /**
     * 采集文章列表
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function collectFormArticle(RequestInterface $request) : ResponseInterface
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);
        $bookId = (int)$request->query('bookId');

        $data          = [];
        $data['list']  = (new CollectService())->getCollectFormList($bookId, 0, $page, $size);
        $data['total'] = (new CollectService())->getCollectFormListCount($bookId, 0);

        return $this->success($data);
    }

    /**
     * 批量确认采集章节
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function batchConfirmCollectArticle(RequestInterface $request) : ResponseInterface
    {
//        $ids = (array) $request->input('ids');
//        if (empty($ids)) {
//            throw new ManageException(ErrorCode::PARAMS_FAILED);
//        }
//        $row = (new CollectService())->confirmCollect($ids);
//        if (!$row) {
//            throw new ManageException(ErrorCode::CONFIRM_FAILED);
//        }
        return $this->success();
    }
}