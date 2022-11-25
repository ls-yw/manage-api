<?php
declare(strict_types = 1);

namespace App\Controller\Blog;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Blog\ArticleService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ArticleController extends BaseController
{
    public function list(RequestInterface $request) : ResponseInterface
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        $data          = [];
        $data['list']  = (new ArticleService())->getList($page, $size);
        $data['total'] = (new ArticleService())->getListCount();

        return $this->success($data);
    }

    public function delete(RequestInterface $request) : ResponseInterface
    {
        $id = (int) $request->input('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new ArticleService())->saveArticle(['id' => $id, 'is_deleted' => 1]);
        if (empty($row)) {
            throw new ManageException(ErrorCode::DELETE_FAILED);
        }
        return $this->success();
    }

    public function recovery(RequestInterface $request) : ResponseInterface
    {
        $id = (int) $request->input('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        $row = (new ArticleService())->saveArticle(['id' => $id, 'is_deleted' => 0]);
        if (empty($row)) {
            throw new ManageException(ErrorCode::RECOVERY_FAILED);
        }
        return $this->success();
    }

    public function info(RequestInterface $request) : ResponseInterface
    {
        $id = (int) $request->input('id');
        if (empty($id)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }

        $data = (new ArticleService())->getInfo($id);

        return $this->success(['data' => $data]);
    }

    public function save(RequestInterface $request) : ResponseInterface
    {
        $data = [
            'id'          => (int) $request->input('id'),
            'title'       => (string) $request->input('title'),
            'desc'        => (string) $request->input('desc'),
            'category_id' => (int) $request->input('category_id'),
            'content'     => (string) $request->input('content'),
            'tags'        => (string) $request->input('tags'),
            'img_url'     => (string) $request->input('img_url'),
            'is_deleted'  => (int) $request->input('is_deleted'),
        ];

        if (empty($data['title']) || empty($data['category_id']) || empty($data['content'])){
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }

        if (empty($data['desc'])) {
            $data['desc'] = substr(strip_tags($data['content']), 0, 150);
        }

        if (empty($data['img_url'])) {
            preg_match('/<img.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/i', $data['content'], $matches);
            if (count($matches) > 1) {
                $data['img_url'] = $matches[1];
            }
        }

        $row = (new ArticleService())->saveArticle($data);
        if (empty($row)) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }

        return $this->success(['data' => $data]);
    }
}