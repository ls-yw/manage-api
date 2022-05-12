<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Novel\ArticleService;
use App\Services\Novel\BookService;
use App\Services\Novel\CollectService;
use App\Utils\Helper\HelperArray;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BookController extends BaseController
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

        $type    = (string) $request->query('type');
        $keyword = (string) $request->query('keyword');

        $data          = [];
        $data['list']  = (new BookService())->getList($type, $keyword, null, $page, $size);
        $data['total'] = (new BookService())->getListCount($type, $keyword, null);

        if (!empty($data['list'])) {
            foreach ($data['list'] as $key => $value) {
                $data['list'][$key]['waitArticleNum'] = (new CollectService())->getCollectFormListCount($value['id'], 0);
                $data['list'][$key]['ossArticleNum']  = (new ArticleService())->getCountByOss($value['id'], 0);
            }
        }

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
            'id'           => (int) $request->input('id'),
            'name'         => trim($request->input('name')),
            'category'     => (int) $request->input('category'),
            'author'       => trim($request->input('author')),
            'is_finished'  => (int) $request->input('is_finished'),
            'intro'        => trim($request->input('intro')),
            'is_collect'   => (int) $request->input('is_collect'),
            'thumb_img'    => trim($request->input('thumb_img')),
            'is_recommend' => (int) $request->input('is_recommend'),
            'quality'      => (int) $request->input('quality'),
        ];

        if (empty($data['name'])) {
            throw new ManageException(ErrorCode::EMPTY_NAME);
        }
        if (empty($data['thumb_img'])) {
            throw new ManageException(ErrorCode::EMPTY_THUMB_IMG);
        }

        $row = (new BookService())->save($data);
        if (!$row) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }
        return $this->success();
    }

    /**
     * 删除
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function delete(RequestInterface $request) : ResponseInterface
    {
        $id = (int) $request->input('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new BookService())->delete($id);
        if (!$row) {
            throw new ManageException(ErrorCode::DELETE_FAILED);
        }
        return $this->success();
    }

    /**
     * 章节数组
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function chapterPairs(RequestInterface $request) : ResponseInterface
    {
        $bookId = (int) $request->query('bookId');

        $data = empty($bookId) ? [] : (new BookService())->getChapterAll($bookId);

        return $this->success(['data' => HelperArray::showPair($data, 'id', 'name')]);
    }

    /**
     * 变更采集状态
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function changeCollect(RequestInterface $request) : ResponseInterface
    {
        $id        = (int) $request->input('id');
        $isCollect = (int) $request->input('isCollect');
        $row       = (new BookService())->changeCollect($id, $isCollect);
        if (!$row) {
            throw new ManageException(ErrorCode::CHANGE_FAILED);
        }
        return $this->success();
    }
}