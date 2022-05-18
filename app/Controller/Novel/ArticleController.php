<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Novel\ArticleService;
use App\Services\Novel\BookService;
use App\Services\Novel\CollectService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ArticleController extends BaseController
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

        $bookId = (int) $request->query('bookId');
        if (empty($bookId)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }

        $data          = [];
        $data['list']  = (new ArticleService())->getList($bookId, $page, $size);
        $data['total'] = (new ArticleService())->getListCount($bookId);

        return $this->success($data);
    }

    /**
     * 获取章节内容
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function getContent(RequestInterface $request) : ResponseInterface
    {
        $articleId = (int) $request->query('articleId');
        if (empty($articleId)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }

        $data = (new ArticleService())->getContent($articleId);
        return $this->success(['data' => $data]);
    }

    /**
     * 保存
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function save(RequestInterface $request) : ResponseInterface
    {
        $data    = [
            'id'         => (int) $request->input('id'),
            'title'      => (string) $request->input('title'),
            'chapter_id' => (int) $request->input('chapter_id'),
            'sort'       => (int) $request->input('sort'),
            'book_id'    => (int) $request->input('book_id'),
        ];
        $collectFromId = (int)$request->input('collect_from_id');
        $content = $request->input('content');
        if (empty($content)) {
            throw new ManageException(ErrorCode::EMPTY_ARTICLE_CONTENT);
        }
        $data['wordnumber'] = mb_strlen(strip_tags($content), mb_detect_encoding($content));

        $oldChapterId = 0;
        $newChapterId = $data['chapter_id'];
        if (!empty($data['id'])) {
            $article      = (new ArticleService())->getById($data['id']);
            $oldChapterId = (int) $article['chapter_id'];
        }
        if ($oldChapterId !== $newChapterId) {
            if (!empty($oldChapterId)) {
                (new BookService())->updateChapterArticleNum($oldChapterId, 'decr', 1);
            }
            (new BookService())->updateChapterArticleNum((int) $newChapterId, 'incr', 1);
        }

        $row = (new ArticleService())->save($data, $content);
        if (!$row) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }
        if (!empty($collectFromId)) {
            (new CollectService())->confirmCollect($collectFromId);
        }
        return $this->success();
    }

    /**
     * 删除章节
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
        $row = (new ArticleService())->delete($id);
        if (!$row) {
            throw new ManageException(ErrorCode::DELETE_FAILED);
        }
        return $this->success();
    }

}