<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Novel\Article;
use App\Model\Novel\Book;
use App\Model\Novel\BookApply;
use App\Model\Novel\Chapter;
use App\Services\Novel\ArticleService;
use App\Services\Novel\CollectRuleService;
use App\Utils\Log\Log;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    /**
     * 注入文件系统
     *
     * @Inject
     * @var Filesystem
     */
    public Filesystem $Filesystem;

    public function test(RequestInterface $request): ResponseInterface
    {
//        $endArticle = 208290;
//        $startArticle = 206964;
//        $list = Article::whereBetween("id", [$startArticle, $endArticle])->select(['id', 'chapter_id', 'book_id', 'wordnumber'])->get();
//        $data['data'] = $list;
//        $data['count'] = count($list);
//        foreach ($list as $value) {
//            $content = (new ArticleService())->getContent($value['id']);
//            $content = (new CollectRuleService())->tmpFilterContent(6, $content);
//
//            try {
//                $this->Filesystem->write('book/' . $value['book_id'] . '/' . $value['id'] . '.txt', $content);
//            } catch (FilesystemException | UnableToWriteFile | \Exception $e) {
//                Log::error('写入小说内容失败', 'oss');
//                Log::error($e->getMessage(), 'oss');
//                throw new ManageException(ErrorCode::WRITE_OSS_OF_CONTENT);
//            }
////            $row = (new ArticleService())->delete((int)$value['id']);
////            if (empty($row)) {
////                throw new ManageException(1, $value['id'].'文章删除失败');
////            }
//        }
        $article = Article::where('book_id', 132)->select('id')->get();
        $data = [];
        foreach ($article as $value) {
            $content = (new ArticleService())->getContent($value['id']);
            $content = nl2br($content);
            $data['data'] = $content;
            try {
                $this->Filesystem->write('book/' . 132 . '/' . $value['id'] . '.txt', $content);
            } catch (FilesystemException | UnableToWriteFile | \Exception $e) {
                Log::error('写入小说内容失败', 'oss');
                Log::error($e->getMessage(), 'oss');
                throw new ManageException(ErrorCode::WRITE_OSS_OF_CONTENT);
            }
        }
        return $this->success($data);
    }
}