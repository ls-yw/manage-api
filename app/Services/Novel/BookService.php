<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Novel\Article;
use App\Model\Novel\Book;
use App\Model\Novel\Chapter;

class BookService extends BaseService
{
    /**
     * 获取列表
     *
     * @author yls
     * @param string   $keywordFieldName
     * @param string   $keyword
     * @param int|null $isCollect
     * @param int      $page
     * @param int      $size
     * @return object
     */
    public function getList(string $keywordFieldName, string $keyword, ?int $isCollect, int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        $where  = $this->_getListWhere($keywordFieldName, $keyword, $isCollect);
        $list   = Book::where($where)->orderBy('id', 'desc')->offset($offset)->limit($size)->get();
        return $this->fillNameToModel($list, Book::class);
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @param string   $keywordFieldName
     * @param string   $keyword
     * @param int|null $isCollect
     * @return int
     */
    public function getListCount(string $keywordFieldName, string $keyword, ?int $isCollect) : int
    {
        $where = $this->_getListWhere($keywordFieldName, $keyword, $isCollect);
        return Book::where($where)->count();
    }

    /**
     * 获取列表条件
     *
     * @author yls
     * @param string   $keywordFieldName
     * @param string   $keyword
     * @param int|null $isCollect
     * @return array
     */
    private function _getListWhere(string $keywordFieldName, string $keyword, ?int $isCollect) : array
    {
        $where = [];
        if (!empty($keyword)) {
            $where[] = [$keywordFieldName, 'like', '%' . $keyword . '%'];
        }
        if (null !== $isCollect) {
            $where['is_collect'] = $isCollect;
        }
        return $where;
    }

    /**
     * 保存
     *
     * @author yls
     * @param array $data
     * @return int
     */
    public function save(array $data) : int
    {
        return $this->saveData($data, Book::make());
    }

    /**
     * 删除
     *
     * @author yls
     * @param int $id
     * @return int
     */
    public function delete(int $id) : int
    {
        $articleNum = (new ArticleService())->getListCount($id);
        if ($articleNum > 0) {
            throw new ManageException(ErrorCode::DELETE_BOOK_HAVE_ARTICLE);
        }

        $bookInfo = Book::find($id);
        // TODO
        //        (new Chapter())->delData(['book_id' => $bookId]);
        //        (new CollectLogic())->delCollectFrom($bookId, (int)$bookInfo['book_collect_id']);
        return Book::where('id', $id)->delete();
    }

    /**
     * 获取章节
     *
     * @author yls
     * @param int $bookId
     * @return object
     */
    public function getChapterAll(int $bookId) : object
    {
        return Chapter::where('book_id', $bookId)->get();
    }

    /**
     * 更新章节数
     *
     * @author yls
     * @param int    $chapterId
     * @param string $type
     * @param int    $num
     * @return int
     */
    public function updateChapterArticleNum(int $chapterId, string $type, int $num) : int
    {
        $model = Chapter::where(['id', $chapterId]);
        return 'incr' === $type ? $model->increment('chapter_articlenum', $num) :  $model->decrement('chapter_articlenum', $num);
    }

    /**
     * 更新小说章节数和字数
     *
     * @author woodlsy
     * @param int $bookId
     * @return int
     */
    public function updateBookArticleNumAndWordsNumber(int $bookId):int
    {
        $articleCount = (new ArticleService())->getListCount($bookId);
        $wordsNumber  = (int)Article::where(['book_id' => $bookId])->sum('wordnumber');
        return Book::where('id', $bookId)->update(['book_articlenum' => $articleCount, 'book_wordsnumber' => $wordsNumber]);
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
        return Book::find($id);
    }
}