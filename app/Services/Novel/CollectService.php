<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Constants\RedisKeyConstant;
use App\Exception\ManageException;
use App\Model\Novel\Book;
use App\Model\Novel\Chapter;
use App\Model\Novel\Collect;
use App\Model\Novel\CollectFrom;
use App\Model\Novel\CollectRule;
use App\Utils\Helper\HelperArray;
use App\Utils\Helper\HelperHttp;
use App\Utils\Log\Log;
use App\Utils\Redis\Redis;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Sender;
use Swoole\WebSocket\Frame;

class CollectService extends BaseService
{
    /**
     * @Inject
     * @var Sender
     */
    protected Sender $sender;

    /**
     * 获取列表
     *
     * @author yls
     * @param int $page
     * @param int $size
     * @return object
     */
    public function getList(int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        $list   = Collect::where('is_deleted', 0)->orderBy('id', 'desc')->offset($offset)->limit($size)->get();
        return $this->fillNameToModel($list, Collect::class);
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @return int
     */
    public function getListCount() : int
    {
        return Collect::where('is_deleted', 0)->count();
    }

    /**
     * 获取详情
     *
     * @author yls
     * @param int $id
     * @return object
     */
    public function getInfo(int $id) : object
    {
        $info = $this->getById($id);
        if (empty($info)) {
            throw new ManageException(ErrorCode::NO_FOUND_DATA);
        }
        $info['rules'] = (new CollectRuleService())->getByCollectId($id);
        return $info;
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
        return Collect::find($id);
    }

    /**
     * 采集文章列表
     *
     * @author yls
     * @param int $bookId
     * @param int $status
     * @param int $page
     * @param int $size
     * @return object
     */
    public function getCollectFromList(int $bookId, int $status, int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        $list   = CollectFrom::where(['book_id' => $bookId, 'from_status' => $status])->orderBy('from_sort')->offset($offset)->limit($size)->get();
        return $this->fillNameToModel($list, CollectFrom::class);
    }

    /**
     * 获取带采集
     *
     * @author yls
     * @param int $bookId
     * @param int $status
     * @return int
     */
    public function getCollectFromListCount(int $bookId, int $status) : int
    {
        return CollectFrom::where(['book_id' => $bookId, 'from_status' => $status])->count();
    }

    /**
     * 根据采集状态获取待采集列表
     *
     * @author yls
     * @param int  $bookId
     * @param int  $status
     * @param bool $update
     * @return array
     */
    public function getCollectFormAllByStatus(int $bookId, int $status, bool $update = false) : array
    {
        $key = RedisKeyConstant::WAIT_COLLECT_FORM_LIST . $bookId . '_' . $status;
        if (!Redis::getInstance()->exists($key) || true === $update) {
            $collectForm = CollectFrom::where(['book_id' => $bookId, 'from_status' => $status])->orderBy('from_sort')->get();
            if (empty($collectForm)) {
                return [];
            }
            $row = Redis::getInstance()->setEx($key, 86400, HelperArray::jsonEncode($collectForm));
            if (!$row) {
                Log::error($key . ' 保存数据失败');
                echo $key . ' 保存数据失败' . PHP_EOL;
                return [];
            }
        }
        return HelperArray::jsonDecode(Redis::getInstance()->get($key));
    }

    /**
     * 保存采集规则
     *
     * @author yls
     * @param array $data
     * @param array $rules
     * @return int
     */
    public function save(array $data, array $rules) : int
    {
        $id         = $data['id'] ?? 0;
        $collectRow = $this->saveData($data, Collect::make());
        if (empty($id) && empty($collectRow)) {
            return $collectRow;
        }
        if (empty($id)) {
            $id = $collectRow;
        }
        $rules['collect_id'] = $id;
        $collectRuleRow      = $this->saveData($rules, CollectRule::make());
        return $collectRow === 0 && $collectRuleRow === 0 ? 0 : 1;
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
        return Collect::where('id', $id)->update(['is_deleted' => 1]);
    }

    /**
     * 确认采集ID
     *
     * @author yls
     * @param array $ids
     * @return int
     */
    public function confirmCollect(array $ids) : int
    {
        return CollectFrom::whereIn('id', $ids)->update(['from_status' => 1]);
    }

    /**
     * 删除采集from
     *
     * @author yls
     * @param int $bookId
     * @param int $collectId
     * @return int
     */
    public function deleteCollectFrom(int $bookId, int $collectId) : int
    {
        return CollectFrom::where(['book_id' => $bookId, 'collect_id' => $collectId])->delete();
    }

    /**
     * 采集测试
     *
     * @author yls
     * @param int    $collectId
     * @param int    $type
     * @param string $url
     * @return array
     */
    public function test(int $collectId, int $type, string $url) : array
    {
        if (1 === $type) {
            return (new CollectRuleService())->collectBookInfo($collectId, $url);
        } elseif (2 === $type) {
            $list = (new CollectRuleService())->collectArticleList($collectId, $url);
            $data = [];
            if (!empty($list)) {
                foreach ($list['id'] as $key => $value) {
                    $data[$value] = $list['title'][$key] . ' | ' . $list['link'][$key];
                }
            }
            return $data;
        } else {
            return (new CollectRuleService())->collectArticleContent($collectId, $url);
        }
    }

    /**
     * 采集小说详情
     *
     * @author yls
     * @param int    $collectId
     * @param string $fromBookId
     * @return array
     */
    public function collectBookInfo(int $collectId, string $fromBookId) : array
    {
        $collect = $this->getById($collectId);
        if (empty($collect)) {
            throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES);
        }
        $collectRule = (new CollectRuleService())->getByCollectId($collectId);
        if (empty($collectRule)) {
            throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES);
        }
        $url = (new CollectRuleService())->dealUrlTags($collectRule['book_url'], $collectRule['sub_book_id'], $fromBookId);
        return (new CollectRuleService())->collectBookInfo($collectId, $url);
    }

    /**
     * 保存采集的小说详情
     *
     * @author yls
     * @param int   $collectId
     * @param array $collectBookInfo
     * @return int
     */
    public function collectSaveBookInfo(int $collectId, array $collectBookInfo) : int
    {
        $book = (new BookService())->getByNameAndAuthor($collectBookInfo['name'], $collectBookInfo['author']);
        if (!empty($book)) {
            if ((int) $book['collect_id'] !== (int) $collectId) {
                throw new ManageException(ErrorCode::COLLECT_ID_NO_EQ);
            }
            return (int) $book['id'];
        }
        // 保存远程缩略图
        $url    = env('UPLOAD_URL') . '/upload/urlImg?project=novel&url=' . $collectBookInfo['thumb_img'];
        $res    = (new HelperHttp())->get($url);
        $result = HelperArray::jsonDecode($res);
        if (!isset($result['code']) || 0 !== $result['code']) {
            Log::error('上传封面图片失败', 'collect');
            Log::error($url, 'collect');
            Log::error($res, 'collect');
            throw new ManageException(ErrorCode::UPLOAD_THUMB_IMG);
        }
        $collectBookInfo['thumb_img'] = env('UPLOAD_URL') . '/' . $result['url'];
        return $this->saveData($collectBookInfo, Book::make());
    }

    /**
     * 开始采集
     *
     * @author yls
     * @param int        $bookId
     * @param Frame|null $frame
     */
    public function startCollect(int $bookId, ?Frame $frame = null) : void
    {
        $bookInfo = (new BookService())->getById($bookId);
        if (empty($bookInfo->collect_id)) {
            $this->_pushCollectMessage($frame, '该小说非采集小说', 'red');
            return;
        }
        $this->_pushCollectMessage($frame, '开始采集小说《'.$bookInfo->name.'》', '', 'row');

        $collectRule = (new CollectRuleService())->getByCollectId((int) $bookInfo->collect_id);
        if (empty($collectRule)) {
            $this->_pushCollectMessage($frame, '该小说的采集规则不存在', 'red');
            return;
        }
        $url                = (new CollectRuleService())->dealUrlTags($collectRule->chapter_url, $collectRule->sub_book_id, $bookInfo->from_collect_book_id);
        $collectArticleList = (new CollectRuleService())->collectArticleList((int) $bookInfo->collect_id, $url);
        if (empty($collectArticleList)) {
            $this->_pushCollectMessage($frame, '采集不到该小说的章节列表', 'red');
            return;
        }

        $collectForm = CollectFrom::where(['book_id' => $bookId, 'collect_id' => $bookInfo['collect_id']])->get()->toArray();
        $this->_updateCollectForm($bookId, (int) $bookInfo->collect_id, $collectForm, $collectArticleList, $frame);
        $collectForm = $this->getCollectFormAllByStatus($bookId, 0, true);
        $this->_pushCollectMessage($frame, '共有' . count($collectForm) . '篇章节需要采集', '', 'row');
        $chapter = $this->getChapter($bookId, $frame);
        if (empty($chapter)) {
            return;
        }
        $index = 0;
        while (isset($collectForm[$index])) {
            $res = $this->collectArticle($bookId, $index, $bookInfo['from_collect_book_id'], (int) $chapter['id'], $frame);
            if (!$res) {
                break;
            }
            $index++;
        }
        $this->_pushCollectMessage($frame, '采集结束', '', 'row');
    }

    /**
     * 采集文章
     *
     * @author yls
     * @param int        $bookId
     * @param int        $collectFormIndex
     * @param string     $fromBookId
     * @param int        $chapterId
     * @param Frame|null $frame
     * @return bool
     */
    public function collectArticle(int $bookId, int $collectFormIndex, string $fromBookId, int $chapterId, ?Frame $frame) : bool
    {
        $collectForm = $this->getCollectFormAllByStatus($bookId, 0);
        if (!isset($collectForm[$collectFormIndex])) {
            $this->_pushCollectMessage($frame, '采集结束', '', 'row');
            return false;
        }
        $form        = $collectForm[$collectFormIndex];
        $collectRule = (new CollectRuleService())->getByCollectId((int) $form['collect_id']);
        if (empty($collectRule)) {
            $this->_pushCollectMessage($frame, '该小说的采集规则不存在', 'red');
            return false;
        }
        $url            = (new CollectRuleService())->dealUrlTags($collectRule->article_url, $collectRule->sub_book_id, $fromBookId, (string) $form['from_article_id']);
        $articleContent = (new CollectRuleService())->collectArticleContent((int) $form['collect_id'], $url);
        if ($articleContent['wordsNumber'] > 200) {
            $article = [
                'title'      => $form['from_title'],
                'chapter_id' => $chapterId,
                'book_id'    => $bookId,
                'sort'       => $form['from_sort'],
                'wordnumber' => $articleContent['wordsNumber'],
            ];
            $row     = (new ArticleService())->save($article, $articleContent['content']);
            if (empty($row)) {
                $this->_pushCollectMessage($frame, $form['from_title'] . '（保存失败）');
            } else {
                CollectFrom::where(['id' => $form['id']])->update(['from_status' => 1]);

                $row = Chapter::where('id', $chapterId)->increment('articlenum');
                $this->_pushCollectMessage($frame, $form['from_title']);
            }
        } else {
            $this->_pushCollectMessage($frame, $form['from_title'] . '（采集失败内容过少）[' . $form['from_sort'] . ']', '', 'col', [
                'from_id'    => $form['id'],
                'from_title' => $form['from_title'],
                'from_url' => $form['from_url'],
                'from_sort'  => $form['from_sort'],
            ]);
        }
        return true;
    }

    /**
     * 获取章节
     *
     * @author yls
     * @param int        $bookId
     * @param Frame|null $frame
     * @return array
     */
    public function getChapter(int $bookId, ?Frame $frame) : array
    {
        $chapter = (new BookService())->getChapterAll($bookId)->toArray();
        if (!empty($chapter)) {
            return end($chapter);
        }
        $chapter               = [];
        $chapter['name']       = '默认章节';
        $chapter['book_id']    = $bookId;
        $chapter['book_name']  = (new BookService())->getBookNameById($bookId);
        $chapter['articlenum'] = 0;
        $chapter['sort']       = 1;
        $chapterId             = (new BookService())->saveChapter($chapter);
        if (empty($chapterId)) {
            $this->_pushCollectMessage($frame, '新增默认章节失败', '', 'row');
            return [];
        }
        $this->_pushCollectMessage($frame, '新增默认章节', '', 'row');
        $chapter['id'] = $chapterId;
        return $chapter;
    }

    /**
     * 推送采集消息
     *
     * @author yls
     * @param Frame|null $frame
     * @param string     $message
     * @param string     $class
     * @param string     $type
     * @param array      $data
     */
    private function _pushCollectMessage(?Frame $frame, string $message, string $class = '', string $type = 'col', array $data = []) : void
    {
        if (null !== $frame) {
            $result = ['code' => 0, 'message' => $message, 'type' => $type];
            if (!empty($class)) {
                $result['class'] = $class;
            }
            if (!empty($data)) {
                $result['data'] = $data;
            }
            $this->sender->push($frame->fd, HelperArray::jsonEncode($result));
        } else {
            echo $message . PHP_EOL;
        }
    }

    /**
     * 更新待采集章节列表
     *
     * @author yls
     * @param int        $bookId
     * @param int        $collectId
     * @param array|null $collectForm
     * @param array      $collectArticleList
     * @param Frame|null $frame
     */
    private function _updateCollectForm(int $bookId, int $collectId, ?array $collectForm, array $collectArticleList, ?Frame $frame) : void
    {
        $endForm = [];
        $sort    = 1;
        if (!empty($collectForm)) {
            $endForm = end($collectForm);
            $sort    = $endForm['from_sort'];
        }
        $insertData = [];
        $fen        = false;
        foreach ($collectArticleList['id'] as $key => $value) {
            if (!empty($endForm)) {
                if ((int) $endForm['from_article_id'] === $value) {
                    $fen = true;
                }
                if (!$fen) {
                    continue;
                }
            }
            $insertData[] = [
                'book_id'         => $bookId,
                'collect_id'      => $collectId,
                'from_article_id' => $value,
                'from_url'        => $collectArticleList['link'][$key],
                'from_sort'       => $sort,
                'from_title'      => $collectArticleList['title'][$key],
                'from_status'     => 0,
            ];
            $sort++;
        }
        if (empty($insertData)) {
            $this->_pushCollectMessage($frame, '无新增待采集的章节列表', '', 'row');
            return;
        }
        $res     = CollectFrom::insert($insertData);
        $message = '新增待' . count($insertData) . '条采集的章节列表' . ($res ? '成功' : '失败');
        $this->_pushCollectMessage($frame, $message, '', 'row');
        Redis::getInstance()->del(RedisKeyConstant::WAIT_COLLECT_FORM_LIST . $bookId . '_0');
    }
}