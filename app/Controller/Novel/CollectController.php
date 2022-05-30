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

    /**
     * 根据ID获取详情
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function info(RequestInterface $request) : ResponseInterface
    {
        $id = (int) $request->query('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $data = (new CollectService())->getInfo($id);
        return $this->success(['data' => $data]);
    }

    /**
     * 保存采集规则
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function save(RequestInterface $request) : ResponseInterface
    {
        $data = [
            'id'             => (int) $request->input('id'),
            'name'           => (string) $request->input('name'),
            'host'           => (string) $request->input('host'),
            'iconv'          => (string) $request->input('iconv'),
            'collect_status' => (int) $request->input('collect_status'),
        ];
        if (empty($data['name']) || empty($data['host']) || empty($request->input('rules'))) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $rules = [
            'id'               => (int) $request->input('rules.id'),
            'book_url'         => (string) $request->input('rules.book_url'),
            'sub_book_id'      => (string) $request->input('rules.sub_book_id'),
            'chapter_url'      => (string) $request->input('rules.chapter_url'),
            'article_url'      => (string) $request->input('rules.article_url'),
            'name'             => (string) $request->input('rules.name'),
            'author'           => (string) $request->input('rules.author'),
            'intro'            => (string) $request->input('rules.intro'),
            'thumb_img'        => (string) $request->input('rules.thumb_img'),
            'filter_thumb_img' => (string) $request->input('rules.filter_thumb_img'),
            'finished'         => (string) $request->input('rules.finished'),
            'category'         => (string) $request->input('rules.category'),
            'article_id'       => (string) $request->input('rules.article_id'),
            'article_title'    => (string) $request->input('rules.article_title'),
            'content'          => (string) $request->input('rules.content'),
            'content_filter'   => (string) $request->input('rules.content_filter'),
            'content_replace'  => (string) $request->input('rules.content_replace'),
        ];
        $row   = (new CollectService())->save($data, $rules);
        if (empty($row)) {
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
        $row = (new CollectService())->delete($id);
        if (empty($row)) {
            throw new ManageException(ErrorCode::DELETE_FAILED);
        }
        return $this->success();
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
        $page   = (int) $request->query('page', 1);
        $size   = (int) $request->query('size', 20);
        $bookId = (int) $request->query('bookId');

        $data          = [];
        $data['list']  = (new CollectService())->getCollectFromList($bookId, 0, $page, $size);
        $data['total'] = (new CollectService())->getCollectFromListCount($bookId, 0);

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
        $ids = (array) $request->input('ids');
        if (empty($ids)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new CollectService())->confirmCollect($ids);
        if (!$row) {
            throw new ManageException(ErrorCode::CONFIRM_FAILED);
        }
        return $this->success();
    }

    /**
     * 测试采集
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function test(RequestInterface $request) : ResponseInterface
    {
        $collectId = (int) $request->input('collectId');
        $type      = (int) $request->input('type');
        $url       = (string) $request->input('url');
        $data      = (new CollectService())->test($collectId, $type, $url);
        return $this->success(['data' => $data]);
    }

    /**
     * 采集小说详情
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function collectBookInfo(RequestInterface $request) : ResponseInterface
    {
        $collectId  = (int) $request->input('collectId');
        $fromBookId = (string) $request->input('fromBookId');

        if (empty($collectId) || empty($fromBookId)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }

        $data = (new CollectService())->collectBookInfo($collectId, $fromBookId);
        return $this->success(['data' => $data]);
    }

    /**
     * 保存采集小说详情
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function collectSaveBookInfo(RequestInterface $request) : ResponseInterface
    {
        $collectId = (int) $request->input('collect_id');
        $data      = [
            'name'                 => (string) $request->input('name'),
            'author'               => (string) $request->input('author'),
            'intro'                => (string) $request->input('intro'),
            'thumb_img'            => (string) $request->input('thumb_img'),
            'is_finished'          => (int) $request->input('finished'),
            'category'             => (int) $request->input('category'),
            'is_collect'           => (int) $request->input('is_collect'),
            'collect_id'           => $collectId,
            'from_collect_book_id' => (int) $request->input('from_collect_book_id'),
        ];

        if (empty($data['name']) || empty($data['author']) || empty($data['category']) || empty($collectId)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }

        $bookId = (new CollectService())->collectSaveBookInfo($collectId, $data);
        if (empty($bookId)) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }

        return $this->success(['data' => $bookId]);
    }


}