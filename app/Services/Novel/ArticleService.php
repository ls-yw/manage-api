<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Novel\Article;
use App\Model\Novel\Book;
use App\Model\Novel\Chapter;
use App\Model\Novel\CollectFrom;
use App\Utils\Log\Log;
use Exception;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;
use Swoole\WebSocket\Frame;

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
        $list   = Article::where('book_id', $bookId)->orderBy('sort')->offset($offset)->limit($size)->get();
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
            } catch (FilesystemException|UnableToWriteFile|\Exception $e) {
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
            Book::where('id', $data['book_id'])->increment('articlenum');
            Book::where('id', $data['book_id'])->increment('wordsnumber', $data['wordnumber']);
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
        } catch (FilesystemException|UnableToWriteFile|\Exception $e) {
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
    protected function updateArticleSort(int $bookId, int $articleSort) : void
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
    public function delete(int $id) : int
    {
        $articleInfo = $this->getById($id);
        if (1 === (int) $articleInfo['is_oss']) {
            try {
                var_dump('book/' . $articleInfo['book_id'] . '/' . $articleInfo['id'] . '.txt');
                $this->Filesystem->delete('book/' . $articleInfo['book_id'] . '/' . $articleInfo['id'] . '.txt');
            } catch (FilesystemException|UnableToWriteFile|\Exception $e) {
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

    /**
     * 获取未上传OSS文章数
     *
     * @author yls
     * @param int $bookId
     * @param int $oss
     * @return int
     */
    public function getCountByOss(int $bookId, int $oss) : int
    {
        return Article::where('book_id', $bookId)->where('is_oss', $oss)->count();
    }

    /**
     * 获取oss条件下的章节
     *
     * @author yls
     * @param int      $bookId
     * @param int|null $isOss
     * @param int      $row
     * @return \Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection
     */
    public function getArticlesByOssRow(int $bookId, ?int $isOss, array $fields, int $row)
    {
        $where = ['book_id' => $bookId];
        if (null !== $isOss) {
            $where['is_oss'] = $isOss;
        }
        return Article::where($where)->select($fields)->limit($row)->orderBy('id')->get();
    }

    /**
     * 情况章节
     *
     * @author yls
     * @param int $bookId
     * @return false[]
     * @throws FilesystemException
     */
    public function clear(int $bookId) : array
    {
        // 先删除oss文件
        $list = $this->getArticlesByOssRow($bookId, 1, ['id', 'is_oss'], 100);

        $data = ['go' => false];
        if (0 !== count($list)) {
            $data['content'] = '删除ID：[' . $list[0]['id'] . '-' . $list[count($list) - 1]['id'] . ']OSS文件';
            try {
                $articleIdArr = [];
                foreach ($list as $value) {
                    $this->Filesystem->delete('book/' . $bookId . '/' . $value['id'] . '.txt');
                    $articleIdArr[] = $value['id'];
                }
                Article::whereIn('id', $articleIdArr)->update(['is_oss' => 0]);
                $data['content'] .= '成功';
                $data['go']      = true;
            } catch (Exception $e) {
                echo $e->getMessage();
                $data['content'] .= '失败';
            }
            return $data;
        }

        $row = Article::where('book_id', $bookId)->delete();

        if (empty($row)) {
            $data['content'] = '清除表文章数据失败';
            return $data;
        }
        (new BookService())->updateBookArticleNumAndWordsNumber($bookId);
        Chapter::where('book_id', $bookId)->delete();

        $book = (new BookService())->getById($bookId);
        CollectFrom::where('book_id', $bookId)->where('collect_id', $book->collect_id)->delete();
        $data['content'] = '清除表文章数据成功';
        return $data;
    }

    /**
     * 获取全部章节
     *
     * @author yls
     * @param int   $bookId
     * @param array $orderBy
     * @param array $fields
     * @return \Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection
     */
    public function getAll(int $bookId, array $orderBy, array $fields)
    {
        return Article::where('book_id', $bookId)->orderBy($orderBy[0], $orderBy[1])->select($fields)->get();
    }

    /**
     * 获取章节ID，若不存在则新增
     *
     * @author yls
     * @param int        $bookId
     * @param string     $chapterName
     * @param int        $sort
     * @param Frame|null $frame
     * @return int
     */
    public function getChapterIdAndAdd(int $bookId, string $chapterName, int $sort, ?Frame $frame = null) : int
    {
        $chapter = Chapter::where(['book_id' => $bookId, 'name' => $chapterName])->get()->toArray();
        if (!empty($chapter)) {
            return current($chapter)['id'];
        }
        $book      = (new BookService())->getById($bookId);
        $data      = [
            'name'      => $chapterName,
            'book_id'   => $bookId,
            'book_name' => $book->name,
            'sort'      => $sort
        ];
        $chapterId = $this->saveData($data, Chapter::make());
        if (!empty($chapterId)) {
            (new CollectService())->pushSocketCollectMessage($frame, '新增章节“' . $chapterName . '”', '', 'row');
        }
        return $chapterId;
    }

}