<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Novel\Article;
use App\Model\Novel\Book;
use App\Utils\Log\Log;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;

class ArticleService extends BaseService
{
    /**
     * 获取列表
     *
     * @author yls
     * @param int $bookId
     * @param int $page
     * @param int $size
     * @return object
     */
    public function getList(int $bookId, int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        $list   = Article::where('book_id', $bookId)->offset($offset)->limit($size)->get();
        return $this->fillNameToModel($list, Article::class);
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @param int $bookId
     * @return int
     */
    public function getListCount(int $bookId) : int
    {
        return Article::where('book_id', $bookId)->count();
    }

    /**
     * 根据ID获取详情
     *
     * @author yls
     * @param int $id
     * @return object
     */
    public function getById(int $id) : object
    {
        return Article::find($id);
    }

    /**
     * 注入文件系统
     *
     * @Inject
     * @var Filesystem
     */
    public Filesystem $Filesystem;

    public function getContent(int $id) : string
    {
        $articleInfo = $this->getById($id);
        if (empty($articleInfo)) {
            throw new ManageException(ErrorCode::NO_FOUND_DATA);
        }
        $bookInfo = (new BookService())->getById($articleInfo['book_id']);
        if (1 === (int) $articleInfo['is_oss']) {
            try {
                return $this->Filesystem->read('book/' . $articleInfo['book_id'] . '/' . $id . '.txt');
            } catch (FilesystemException | UnableToWriteFile | \Exception $e) {
                Log::error('获取小说内容失败', 'oss');
                Log::error($e->getMessage(), 'oss');
                return '';
            }
        } else {

            // TODO 从本地获取内容
            //            $article['content'] = HelperExtend::getBookText($categoryId, $article['book_id'], $id);
        }
        return '';
    }

    /**
     * 保存
     *
     * @author yls
     * @param array  $data
     * @param string $content
     * @return int
     */
    public function save(array $data, string $content) : int
    {
        $articleId = $data['id'] ?? 0;
        if (empty($articleId)) {
            // 判断是否要更新article_sort
            $this->updateArticleSort((int) $data['book_id'], (int) $data['sort']);
            $articleId = $this->saveData($data, Article::make());
            if (empty($articleId)) {
                return 0;
            }
            Book::where('id', $data['book_id'])->increment('book_articlenum');
            Book::where('id', $data['book_id'])->increment('book_wordsnumber', $data['wordnumber']);
        } else {
            $oldArticle = $this->getById($articleId);
            if ((int) $oldArticle['sort'] !== (int) $data['sort']) {
                $this->updateArticleSort((int) $data['book_id'], (int) $data['sort']);
            }
            $row = $this->saveData($data, Article::make());
            if (empty($row)) {
                return 0;
            }
        }
        try {
            $this->Filesystem->write('book/' . $data['book_id'] . '/' . $articleId . '.txt', $content);
        } catch (FilesystemException | UnableToWriteFile | \Exception $e) {
            Log::error('写入小说内容失败', 'oss');
            Log::error($e->getMessage(), 'oss');
            throw new ManageException(ErrorCode::WRITE_OSS_OF_CONTENT);
        }
        $url = 'http://woodlsy-novel.oss-cn-hangzhou-internal.aliyuncs.com/book/' . $data['book_id'] . '/' . $articleId . '.txt';
        Article::where('id', $articleId)->update(['url' => $url, 'is_oss' => 1]);
        return 1;
    }

    /**
     * 更新文章排序
     *
     * @author yls
     * @param int $bookId
     * @param int $articleSort
     */
    protected function updateArticleSort(int $bookId, int $articleSort):void
    {
        $existSort = Article::where(['book_id' => $bookId, 'sort' => $articleSort])->count();
        if (!empty($existSort)) {
            Article::where('book_id', $bookId)->where('sort', '>=', $articleSort)->increment('sort');
        }
    }

    /**
     * 删除文章
     *
     * @author yls
     * @param int $id
     * @return int
     */
    public function delete(int $id):int
    {
        $articleInfo = $this->getById($id);
        if (1 === (int)$articleInfo['is_oss']) {
            try {
                $this->Filesystem->delete('book/' . $articleInfo['book_id'] . '/' . $articleInfo['id'] . '.txt');
            } catch (FilesystemException | UnableToWriteFile | \Exception $e) {
                Log::error('删除oss章节失败', 'oss');
                Log::error($e->getMessage(), 'oss');
                throw new ManageException(ErrorCode::DELETE_OSS_ARTICLE);
            }
        }
        $row = Article::where('id', $id)->delete();
        if (!empty($row)) {
            (new BookService())->updateChapterArticleNum((int) $articleInfo['chapter_id'], 'decr', 1);
            (new BookService())->updateBookArticleNumAndWordsNumber((int) $articleInfo['book_id']);
        }
        return $row;
    }
}