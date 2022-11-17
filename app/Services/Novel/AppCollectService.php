<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Novel\Book;
use App\Model\Novel\Chapter;
use App\Utils\Helper\HelperArray;
use App\Utils\Helper\HelperHttp;
use App\Utils\Helper\HelperString;
use App\Utils\Log\Log;
use Exception;
use Hyperf\Utils\Parallel;
use Swoole\WebSocket\Frame;
use woodlsy\httpClient\HttpCurl;

class AppCollectService extends BaseService
{
    public array $urls = [
        'jbzssq' => [
            'search'  => [
                'url'    => 'https://souxs.leeyegy.com/search.aspx?key={keyword}&page={page}&siteid=app2&appid=iosjbzssq',
                'fields' => [
                    'Id'          => 'id',
                    'Name'        => 'name',
                    'Author'      => 'author',
                    'Img'         => 'thumb_img',
                    'Desc'        => 'desc',
                    'BookStatus'  => 'status',
                    'CName'       => 'category_name',
                    'LastChapter' => 'last_article',
                    'UpdateTime'  => 'last_time',
                ]
            ],
            'chapter' => [
                'url' => 'https://infosxs.pysmei.com/BookFiles/Html/{subBookId}/{fromBookId}/index.html',
            ],
            'article' => [
                'url' => 'https://contentxs.apptuxing.com/BookFiles/Html/{subBookId}/{fromBookId}/{articleId}.html',
            ],
            'info' => [
                'url' => 'https://infosxs.pysmei.com/BookFiles/Html/{subBookId}/{fromBookId}/info.html'
            ],
            'fromBookId' => 0,
            'subBookId' => 0
        ]
    ];

    /**
     * 搜索
     *
     * @author yls
     * @param        $source
     * @param string $keyword
     * @param int    $page
     * @return array
     */
    public function search($source, string $keyword, int $page) : array
    {
        $obj = $this->urls[$source];
        if (empty($obj)) {
            throw new ManageException(ErrorCode::APP_COLLECT_SOURCE_NO_EXITS);
        }
        $url = str_replace(['{keyword}', '{page}'], [$keyword, $page], $obj['search']['url']);
        try {
            $res = (new HttpCurl())->setUrl($url)->randIp()->get();
        } catch (Exception $exception) {
            Log::error($url, 'jbzssq');
            Log::error($exception->getMessage(), 'jbzssq');
            throw new ManageException(ErrorCode::ERROR_CUSTOM, '爬取接口时失败:' . $exception->getMessage());
        }
        $result = HelperArray::jsonDecode($res);
        return $this->jbzssqSearchResult($result);
    }

    /**
     * 替换搜索字段
     *
     * @author yls
     * @param array $data
     * @param array $fields
     * @return array
     */
    public function replaceSearchFields(array $data, array $fields) : array
    {
        $arr = [];
        foreach ($data as $value) {
            $tmp = [];
            foreach ($fields as $k => $v) {
                $tmp[$v] = $value[$k];
            }
            $arr[] = $tmp;
        }
        return $arr;
    }

    /**
     * 采集详情
     *
     * @author yls
     * @param string $source
     * @param array  $data
     * @return int
     */
    public function collect(string $source, array $data) : int
    {
        $this->checkCollected($source, $data['name'], $data['author']);
        $collect            = (new CollectService())->getByEname($source);
        $data['collect_id'] = $collect->id;
        // 保存远程缩略图
        $data['thumb_img'] = $this->uploadRemoteImage($data['thumb_img']);
        return $this->saveData($data, Book::make());
    }

    /**
     * 检测是否已采集
     *
     * @author yls
     * @param string $source
     * @param string $name
     * @param string $author
     */
    public function checkCollected(string $source, string $name, string $author)
    {
        $collect = (new CollectService())->getByEname($source);
        if (empty($collect)) {
            throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES);
        }
        $bookId = (new CollectService())->checkCollectedByBook($name, $author, $collect->id);
        if (!empty($bookId)) {
            throw new ManageException(ErrorCode::COLLECT_ID_EQ);
        }
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
            (new CollectService())->pushSocketCollectMessage($frame, '该小说非采集小说', 'red');
            return;
        }
        $collect = (new CollectService())->getById((int) $bookInfo->collect_id);
        if (empty($collect) || 2 !== $collect->target_type) {
            (new CollectService())->pushSocketCollectMessage($frame, '该小说的采集规则不存在或者非APP采集', 'red');
            return;
        }
        (new CollectService())->pushSocketCollectMessage($frame, '开始采集小说《' . $bookInfo->name . '》', '', 'row');
        $chapters = $this->collectChapter($bookId, $collect->ename, $bookInfo->from_collect_book_id, $frame);
        $this->collectChapterAndArticle($bookId, $collect->ename, $chapters, $frame);
    }

    /**
     * 采集章节
     *
     * @author yls
     * @param int        $bookId
     * @param string     $targetType
     * @param string     $formBookId
     * @param Frame|null $frame
     * @return array
     */
    public function collectChapter(int $bookId, string $targetType, string $formBookId, ?Frame $frame = null):array
    {
        $chapterUrl = $this->urls[$targetType]['chapter']['url'];
        $subBookId  = $this->jbzssqGetSubBookId((int) $formBookId);
        $this->urls[$targetType]['subBookId'] = $subBookId;
        $this->urls[$targetType]['bookId'] = $formBookId;

        $url = str_replace(['{subBookId}', '{fromBookId}'], [$subBookId, $formBookId], $chapterUrl);
        try {
            $res = (new HttpCurl())->setUrl($url)->randIp()->get();
        } catch (Exception $exception) {
            Log::error($url, 'jbzssq');
            Log::error($exception->getMessage(), 'jbzssq');
            throw new ManageException(ErrorCode::ERROR_CUSTOM, '爬取接口时失败:' . $exception->getMessage());
        }

        return match ($targetType) {
            'jbzssq' => $this->jbzssqChaptersResult($bookId, $res, $frame),
            default => throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES),
        };
    }

    /**
     * 采集章节和文章
     *
     * @author yls
     * @param int        $bookId
     * @param string     $targetType
     * @param array      $chapters
     * @param Frame|null $frame
     */
    public function collectChapterAndArticle(int $bookId, string $targetType, array $chapters, ?Frame $frame = null)
    {
        $parallel = new Parallel(10);

        foreach ($chapters as $key => $value) {
            if (empty($value['list']) || 0 === count($value['list'])) {
                continue;
            }

            $chapterId = (new ArticleService())->getChapterIdAndAdd($bookId, $value['name'], $key+1, $frame);
            foreach ($value['list'] as $val) {
                $parallel->add(function() use ($val, $bookId, $chapterId, $targetType, $frame) {
                    $this->collectArticleContent($val, $bookId, $chapterId, $targetType, $frame);
                });

            }
        }
        $parallel->wait();
        (new BookService())->updateCollectAt($bookId);
        (new CollectService())->pushSocketCollectMessage($frame, '采集结束', 'green', 'row', ['action' => 'closed']);
    }

    /**
     * 采集文章内容
     *
     * @author yls
     * @param array      $fromArticleData
     * @param int        $bookId
     * @param int        $chapterId
     * @param string     $targetType
     * @param Frame|null $frame
     */
    public function collectArticleContent(array $fromArticleData, int $bookId, int $chapterId, string $targetType, ?Frame $frame = null)
    {
        $subBookId = $this->urls[$targetType]['subBookId'];
        $fromBookId = $this->urls[$targetType]['bookId'];
        $url = str_replace(['{subBookId}', '{fromBookId}', '{articleId}'], [$subBookId, $fromBookId, $fromArticleData['id']], $this->urls[$targetType]['article']['url']);
        try {
            $res = (new HttpCurl())->setUrl($url)->randIp()->get();
        } catch (Exception $exception) {
            Log::error($url, 'jbzssq');
            Log::error($exception->getMessage(), 'jbzssq');
            throw new ManageException(ErrorCode::ERROR_CUSTOM, '爬取接口时失败:' . $exception->getMessage());
        }
        if (strlen($res) < 200) {
            (new CollectService())->pushSocketCollectMessage($frame, '获取数据异常：'.$res, '', 'col');
        }
        $articleResult = match ($targetType) {
            'jbzssq' => $this->jbzssqArticleResult($res),
            default => throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES),
        };
        $articleResult['sort'] = $fromArticleData['sort'];
        $articleResult['book_id'] = $bookId;
        $articleResult['chapter_id'] = $chapterId;
        if ($articleResult['wordnumber'] < 200) {
            (new CollectService())->pushSocketCollectMessage($frame, $articleResult['title'] . '（采集失败内容过少）[' . $articleResult['sort'] . ']', '', 'col', [
                'from_id'    => $fromArticleData['id'],
                'from_title' => $articleResult['title'],
                'from_url'   => $url,
                'from_sort'  => $articleResult['sort'],
            ]);
            return;
        }

        $content = $articleResult['content'];
        unset($articleResult['content']);
        $row     = (new ArticleService())->save($articleResult, $content);
        if (empty($row)) {
            (new CollectService())->pushSocketCollectMessage($frame, $articleResult['title'] . '（保存失败）');
        } else {
            Chapter::where('id', $chapterId)->increment('articlenum');
            (new CollectService())->pushSocketCollectMessage($frame, $articleResult['title']);
        }
    }

    public function look(string $source, int $fromBookId):array
    {
        switch ($source) {
            case 'jbzssq':
                return $this->jbzssqLook($fromBookId);
                break;
            default:
                throw new ManageException(ErrorCode::ERROR_CUSTOM, "类型错误");
        }
    }

    public function lookContent(string $source, int $fromBookId, int $fromArticleId):array
    {
        switch ($source) {
            case 'jbzssq':
                return $this->jbzssqLookContent($fromBookId, $fromArticleId);
                break;
            default:
                throw new ManageException(ErrorCode::ERROR_CUSTOM, "类型错误");
        }
    }

    /******************** 旧版看书神器 start *********************/

    /**
     * 旧版追书神器搜索结果处理
     *
     * @author yls
     * @param array $data
     * @return array
     */
    public function jbzssqSearchResult(array $data) : array
    {
        if (1 !== $data['status']) {
            throw new ManageException(ErrorCode::ERROR_CUSTOM, '返回结果失败，错误信息：' . $data['info']);
        }
        return $this->replaceSearchFields($data['data'], $this->urls['jbzssq']['search']['fields']);
    }

    public function jbzssqGetSubBookId(int $bookId)
    {
        return floor($bookId / 1000) + 1;
    }

    public function jbzssqChaptersResult(int $bookId, string $data, ?Frame $frame = null) : array
    {
        $data = HelperString::clearBom($data);
        $data = str_replace(',]', ']', $data);

        $list           = HelperArray::jsonDecode($data);
        $articles       = (new ArticleService())->getAll($bookId, ['sort', 'asc'], ['from_article_id']);
        $fromArticleIdArr = HelperArray::getValueArray($articles->toArray(), 'from_article_id');
        $sourceChapters = $list['data']['list'];
        $sort           = 1;
        foreach ($list['data']['list'] as $key => $value) {
            foreach ($value['list'] as $k => $val) {
                $sourceChapters[$key]['list'][$k]['sort'] = $sort;
                if (0 === (int)$val['hasContent'] || in_array((int)$val['id'], $fromArticleIdArr, true)){
                    unset($sourceChapters[$key]['list'][$k]);
                }
                $sort++;
            }
        }

        unset($list);
        return $sourceChapters;
    }

    public function jbzssqArticleResult(string $res):array
    {
        $res = HelperString::clearBom($res);
        $result = HelperArray::jsonDecode($res);
        if (null === $result) {
            Log::error('json解析结果失败:'.$res, 'jbzssqArticleResult');
            throw new ManageException(ErrorCode::ERROR_CUSTOM, 'json解析结果失败');
        }
        $result = $result['data'];
        return [
            'title' => $result['cname'],
            'from_article_id' => $result['cid'],
            'content' => nl2br($result['content']),
            'wordnumber' => mb_strlen(strip_tags($result['content']))
        ];
    }

    public function jbzssqLook(int $fromBookId):array
    {
        $chapterUrl = $this->urls['jbzssq']['chapter']['url'];
        $subBookId  = $this->jbzssqGetSubBookId((int) $fromBookId);

        $chapterUrl = str_replace(['{subBookId}', '{fromBookId}'], [$subBookId, $fromBookId], $chapterUrl);

            try {
                $res = (new HttpCurl())->setUrl($chapterUrl)->randIp()->get();
            } catch (Exception $exception) {
                Log::error($chapterUrl, 'jbzssq');
                Log::error($exception->getMessage(), 'jbzssq');
                throw new ManageException(ErrorCode::ERROR_CUSTOM, '爬取接口时失败:' . $exception->getMessage());
            }
        $data = HelperString::clearBom($res);
        $data = str_replace(',]', ']', $data);
        $list           = HelperArray::jsonDecode($data);
        $sourceChapters = [];
        foreach ($list['data']['list'] as $key => $value) {
            $sourceChapters[$key] = [
                'name' => $value['name'],
                'bookId' => $fromBookId
            ];
            foreach ($value['list'] as $val) {
                $sourceChapters[$key]['list'][] = [
                    'name' => $val['name'],
                    'id' => $val['id'],
                ];
            }
        }
        return $sourceChapters;
    }

    public function jbzssqLookContent(int $fromBookId, int $fromArticleId){
        $articleUrl = $this->urls['jbzssq']['article']['url'];
        $subBookId  = $this->jbzssqGetSubBookId($fromBookId);
        $articleUrl = str_replace(['{subBookId}', '{fromBookId}', '{articleId}'], [$subBookId, $fromBookId, $fromArticleId], $articleUrl);

        try {
            $res = (new HttpCurl())->setUrl($articleUrl)->randIp()->get();
        } catch (Exception $exception) {
            Log::error($articleUrl, 'jbzssq');
            Log::error($exception->getMessage(), 'jbzssq');
            throw new ManageException(ErrorCode::ERROR_CUSTOM, '爬取接口时失败:' . $exception->getMessage());
        }

        return $this->jbzssqArticleResult($res);
    }

    /******************** 旧版看书神器 end *********************/
}