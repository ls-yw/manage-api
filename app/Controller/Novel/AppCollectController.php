<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Novel\AppCollectService;
use App\Services\Novel\CollectService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppCollectController extends BaseController
{
    /**
     * 搜索
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function search(RequestInterface $request) : ResponseInterface
    {
        $keyword = (string) $request->query('keyword');
        $source  = (string) $request->query('source');
        $page    = (int) $request->query('page', 1);

        if (empty($keyword)) {
            throw new ManageException(ErrorCode::EMPTY_KEYWORD);
        }

        $data         = [];
        $data['data'] = (new AppCollectService())->search($source, $keyword, $page);
        return $this->success($data);
    }

    public function collect(RequestInterface $request) : ResponseInterface
    {
        $source = (string) $request->input('source');
        $data   = [
            'from_collect_book_id' => (string) $request->input('id'),
            'name'                 => (string) $request->input('name'),
            'author'               => (string) $request->input('author'),
            'thumb_img'            => (string) $request->input('thumb_img'),
            'intro'                => (string) $request->input('desc'),
            'category'             => (string) $request->input('category'),
            'is_collect'           => 1,
        ];
        if (empty($source) || empty($data['name']) || empty($data['author']) || empty($data['from_collect_book_id'])) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $status              = (string) $request->input('status');
        $data['is_finished'] = !str_contains($status, '连载') ? 1 : 0;
        $bookId              = (new AppCollectService())->collect($source, $data);
        if (empty($bookId)) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }
        return $this->success(['data' => $bookId]);
    }

    /**
     * 检测是否已采集
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function checkCollected(RequestInterface $request) : ResponseInterface
    {
        $source = (string) $request->input('source');
        $name   = (string) $request->input('name');
        $author = (string) $request->input('author');

        if (empty($source) || empty($name) || empty($author)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        (new AppCollectService())->checkCollected($source, $name, $author);
        return $this->success();
    }

    public function look(RequestInterface $request) : ResponseInterface
    {
        $source = (string) $request->query('source');
        $id     = (int) $request->query('id');

        $data = (new AppCollectService())->look($source, $id);

        return $this->success(['data' => $data]);
    }

    public function lookContent(RequestInterface $request) : ResponseInterface
    {
        $source    = (string) $request->query('source');
        $bookId    = (int) $request->query('bookId');
        $articleId = (int) $request->query('articleId');

        $data = (new AppCollectService())->lookContent($source, $bookId, $articleId);

        return $this->success(['data' => $data]);
    }
}