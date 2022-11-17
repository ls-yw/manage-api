<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Novel\Article;
use App\Model\Novel\Book;
use App\Model\Novel\BookApply;
use App\Model\Novel\Chapter;
use App\Utils\Helper\HelperTime;

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
        Chapter::where('book_id', $id)->delete();
        (new CollectService())->deleteCollectFrom($id, (int) $bookInfo['collect_id']);
        return (int) Book::where('id', $id)->delete();
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
        $model = Chapter::where(['id' => $chapterId]);
        return 'incr' === $type ? $model->increment('articlenum', $num) : $model->decrement('articlenum', $num);
    }

    /**
     * 更新小说章节数和字数
     *
     * @author woodlsy
     * @param int $bookId
     * @return int
     */
    public function updateBookArticleNumAndWordsNumber(int $bookId) : int
    {
        $articleCount = (new ArticleService())->getListCount($bookId);
        $wordsNumber  = (int) Article::where(['book_id' => $bookId])->sum('wordnumber');
        return Book::where('id', $bookId)->update(['articlenum' => $articleCount, 'wordsnumber' => $wordsNumber]);
    }

    /**
     * 更新采集时间
     *
     * @author yls
     * @param int $bookId
     * @return int
     */
    public function updateCollectAt(int $bookId) : int
    {
        return Book::where('id', $bookId)->update(['last_collect_at' => HelperTime::now()]);
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

    /**
     * 变更采集状态
     *
     * @author yls
     * @param int $id
     * @param int $isCollect
     * @return int
     */
    public function changeCollect(int $id, int $isCollect) : int
    {
        return Book::where('id', $id)->update(['is_collect' => $isCollect]);
    }

    /**
     * 获取列表
     *
     * @author yls
     * @param int $page
     * @param int $size
     * @return object
     */
    public function getApplyList(int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        return BookApply::orderBy('id', 'desc')->offset($offset)->limit($size)->get();
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @return int
     */
    public function getApplyListCount() : int
    {
        return BookApply::count();
    }

    /**
     * 申请收录回复
     *
     * @author yls
     * @param int    $id
     * @param int    $bookId
     * @param string $reply
     * @return int
     */
    public function replyApply(int $id, int $bookId, string $reply) : int
    {
        $data = [
            'id'       => $id,
            'book_id'  => $bookId,
            'reply'    => $reply,
            'reply_at' => HelperTime::now()
        ];
        return $this->saveData($data, BookApply::make());
    }

    /**
     * 删除收录申请
     *
     * @author yls
     * @param int $id
     * @return int
     */
    public function deleteApply(int $id) : int
    {
        return (int) BookApply::where('id', $id)->delete();
    }

    /**
     * 根据名称和作者获取小说详情
     *
     * @author yls
     * @param string $name
     * @param string $author
     * @return object|null
     */
    public function getByNameAndAuthor(string $name, string $author) : ?object
    {
        return Book::where(['name' => $name, 'author' => $author])->first();
    }

    /**
     * 根据ID获取小说名称
     *
     * @author yls
     * @param int $bookId
     * @return string
     */
    public function getBookNameById(int $bookId) : string
    {
        return $this->getById($bookId)->name ?? '';
    }

    /**
     * 保存章节
     *
     * @author yls
     * @param array $data
     * @return int
     */
    public function saveChapter(array $data) : int
    {
        return $this->saveData($data, chapter::make());
    }
}