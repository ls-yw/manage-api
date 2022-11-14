<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Novel\CollectRule;
use App\Utils\Helper\HelperHttp;

class CollectRuleService extends BaseService
{

    /**
     * 根据采集ID获取详情
     *
     * @author yls
     * @param int $collectId
     * @return null|object
     */
    public function getByCollectId(int $collectId) : ?object
    {
        return CollectRule::where('collect_id', $collectId)->first();
    }

    public function bu()
    {

    }

    /**
     * 采集小说基本信息
     *
     * @author yls
     * @param int    $collectId
     * @param string $url
     * @return array
     */
    public function collectBookInfo(int $collectId, string $url) : array
    {
        $collectRules = $this->getByCollectId($collectId);
        if (empty($collectRules)) {
            throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES);
        }
        $html = (new HelperHttp())->get($url);

        $bookNameRule     = $this->rulesTagsReplace($collectRules->name);
        $bookAuthorRule   = $this->rulesTagsReplace($collectRules->author);
        $bookIntroRule    = $this->rulesTagsReplace($collectRules->intro);
        $bookThumbImgRule = $this->rulesTagsReplace($collectRules->thumb_img);
        $bookFinishedRule = $this->rulesTagsReplace($collectRules->finished);
        $bookCategoryRule = $this->rulesTagsReplace($collectRules->category);

        preg_match('/' . $bookNameRule . '/i', $html, $resultName);
        preg_match('/' . $bookAuthorRule . '/i', $html, $resultAuthor);
        preg_match('/' . $bookIntroRule . '/i', $html, $resultIntro);
        preg_match('/' . $bookThumbImgRule . '/i', $html, $resultThumbImg);
        preg_match('/' . $bookFinishedRule . '/i', $html, $resultFinished);
        preg_match('/' . $bookCategoryRule . '/i', $html, $resultCategory);

        $book = [
            'name'          => $resultName[1] ?? '',
            'author'        => $resultAuthor[1] ?? '',
            'intro'         => $resultIntro[1] ?? '',
            'thumb_img'     => $resultThumbImg[1] ?? '',
            'finished'      => (int) !empty($resultFinished),
            'category_name' => $resultCategory[1] ?? '',
        ];

        return $book;
    }

    /**
     * 采集章节列表数据
     *
     * @author yls
     * @param int    $collectId
     * @param string $url
     * @return array
     */
    public function collectArticleList(int $collectId, string $url) : array
    {
        $collectRules = $this->getByCollectId($collectId);
        if (empty($collectRules)) {
            throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES);
        }
        $collect = (new CollectService())->getById($collectId);

        $html = (new HelperHttp())->get($url);

        $articleTitleRule = $this->rulesTagsReplace($collectRules->article_title);
        $articleIdRule    = $this->rulesTagsReplace($collectRules->article_id);

        preg_match_all('/' . $articleTitleRule . '/i', $html, $resultArticleTitle);
        preg_match_all('/' . $articleIdRule . '/i', $html, $resultArticleId);

        // 获取链接
        $list    = [
            'title' => $resultArticleTitle[1],
            'id'    => $resultArticleId[1]
        ];
        $linkArr = preg_replace('/(.*)(href[\s]*=\")([^\"]+)(\"[\s\S]*)/i', '$3', $resultArticleTitle[0]);
        if (!empty($linkArr)) {
            foreach ($linkArr as $link) {
                if (!str_starts_with($link, 'http')) { // 不是一个全链接，需要补全
                    if (str_starts_with($link, '/')) { // 以/开头，直接拼接域名
                        $link = (str_ends_with($collect->host, '/') ? substr($collect->host, 0, -1) : $collect->host) . $link;
                    } else {
                        $link = $url . $link;
                    }
                }
                $list['link'][] = $link;
            }
        }

        return $list;
    }

    /**
     * 采集文章内容
     *
     * @author yls
     * @param int    $collectId
     * @param string $url
     * @return array
     */
    public function collectArticleContent(int $collectId, string $url) : array
    {
        $collectRules = $this->getByCollectId($collectId);
        if (empty($collectRules)) {
            throw new ManageException(ErrorCode::NO_EXISTS_COLLECT_RULES);
        }
        $collect = (new CollectService())->getById($collectId);

        $html = (new HelperHttp())->get($url);

        $contentRule = $this->rulesTagsReplace($collectRules->content);
        preg_match('/' . $contentRule . '/i', $html, $resultContent);

        $data = ['content' => '', 'wordsNumber' => 0];

        $data['content'] = $resultContent[1] ?? '';
        $data['content'] = $this->tmpFilterContent($collectId, $data['content']);
        if (empty($data['content'])) {
            return $data;
        }
        $this->_filterArticleContent($data['content'], $collectRules->content_filter, $collectRules->content_replace);
        $contentCode = mb_detect_encoding($data['content']);
        if (mb_detect_encoding($data['content']) != 'UTF-8') {
            $data['content'] = iconv($contentCode, 'UTF-8', $data['content']);
        }
        preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $data['content'], $matches); // 匹配中文
        $str                 = implode('', $matches[0]);
        $data['wordsNumber'] = mb_strlen($str);
        return $data;
    }

    /**
     * 过滤文章内容
     *
     * @author yls
     * @param string $content
     * @param string $filterRule
     * @param string $replaceRule
     */
    private function _filterArticleContent(string &$content, string $filterRule, string $replaceRule) : void
    {
        if (empty($content)) {
            return;
        }
        $content = preg_replace("/<a([^>]*)>(.*)<\/a>/", "", $content);
        $content = preg_replace("'<script(.*?)<\/script>'is", "", $content);

        $filterRules  = explode('|', $filterRule);
        $replaceRules = explode('|', $replaceRule);
        if (empty($filterRules)) {
            return;
        }
        foreach ($filterRules as $key => $value) {
            $content = preg_replace("/$value/i", ($replaceRules[$key] ?? ''), $content);
        }
        return;
    }


    /**
     * 规则标签替换
     *
     * @author yls
     * @param string $rule
     * @return string
     */
    private function rulesTagsReplace(string $rule) : string
    {
        if (empty($rule)) {
            return $rule;
        }
        $rule = addslashes($rule);
        $rule = preg_replace('/\//', '\/', $rule);
        $rule = preg_replace('/\(/', '\(', $rule);
        $rule = preg_replace('/\)/', '\)', $rule);
        $rule = preg_replace('/\./', '\.', $rule);
        $rule = preg_replace('/\?/', '\?', $rule);
        $rule = preg_replace('/(\r\n)|(\n)/', '[\r\n|\n]*', $rule);
        $rule = preg_replace('/@/', '&nbsp;', $rule);

        if (strpos($rule, '!!!!')) {
            $ruleArr    = explode('!!!!', $rule);
            $ruleArr[2] = '([^<>]*)';
        } elseif (strpos($rule, '$$$$')) {
            $ruleArr    = explode('$$$$', $rule);
            $ruleArr[2] = '(\d*)';
        } elseif (strpos($rule, '~~~~')) {
            $ruleArr    = explode('~~~~', $rule);
            $ruleArr[2] = '([^<>\'\"]*)';
        } elseif (strpos($rule, '^^^^')) {
            $ruleArr    = explode('^^^^', $rule);
            $ruleArr[2] = '([^<>\d]*)';
        } elseif (strpos($rule, '****')) {
            $ruleArr    = explode('****', $rule);
            $ruleArr[2] = '([\w\W]*)';
        } else {
            $ruleArr = [];
        }
        if (!empty($ruleArr)) {
            for ($i = 0; $i <= 1; $i++) {
                $ruleArr[$i] = preg_replace('/(?<!\])\*/', '[\s\S]*', $ruleArr[$i]);
                $ruleArr[$i] = preg_replace('/(?<!\<)!/', '[^<>]*', $ruleArr[$i]);
                $ruleArr[$i] = preg_replace('/\$/', '\d*', $ruleArr[$i]);
                $ruleArr[$i] = preg_replace('/~/', '[^<>\'\"]*', $ruleArr[$i]);
                $ruleArr[$i] = preg_replace('/(?<!\[)\^/', '[^<>\d]*', $ruleArr[$i]);
                //$rule[$i] = preg_replace('/\s*/', '\s*', $rule[$i]);
            }
            return $ruleArr[0] . $ruleArr[2] . $ruleArr[1];
        }

        return $rule;
    }

    /**
     * 处理url标签
     *
     * @author yls
     * @param string     $urlRule
     * @param string     $subBookIdRule
     * @param string|int $fromBookId
     * @return string
     */
    public function dealUrlTags(string $urlRule, string $subBookIdRule, string|int $fromBookId, string $articleId = '') : string
    {
        if (!empty($subBookIdRule)) {
            $subBooKIdFormula = preg_replace('/<{bookId}>/i', (string) $fromBookId, $subBookIdRule);
            $subBookId        = $this->computeSubBookId($subBooKIdFormula);
            $urlRule          = preg_replace('/<{subBookId}>/i', $subBookId, $urlRule);
        }
        if (!empty($articleId)) {
            $urlRule = preg_replace('/<{articleId}>/i', $articleId, $urlRule);
        }
        return preg_replace('/<{bookId}>/i', (string) $fromBookId, $urlRule);
    }

    /**
     * 计算子小说ID
     *
     * @author yls
     * @param string $subBooKIdFormula
     * @return int
     */
    private function computeSubBookId(string $subBooKIdFormula) : string
    {
        $jia = explode('+', $subBooKIdFormula);
        if (isset($jia['1']) && $jia['1'] != '') {
            return (string) ($jia[0] + $jia[1]);
        }
        $jian = explode('-', $subBooKIdFormula);
        if (isset($jian['1']) && $jian['1'] != '') {
            return (string) ($jian[0] - $jian[1]);
        }
        $chen = explode('*', $subBooKIdFormula);
        if (isset($chen['1']) && $chen['1'] != '') {
            return (string) ($chen[0] * $chen[1]);
        }
        $chu = explode('%%', $subBooKIdFormula);
        if (isset($chu['1']) && $chu['1'] != '') {
            return (string) floor($chu[0] / $chu[1]);
        }
        $mo = explode('%', $subBooKIdFormula);
        if (isset($mo['1']) && $mo['1'] != '') {
            return (string) ($mo[0] % $mo[1]);
        }
    }

    public function tmpFilterContent(int $collectId, string $content):string
    {
        if ($collectId !== 6) {
            return $content;
        }

        $content = preg_replace('/有的人死了，但没有完全死([\w\W]*)御兽师？/', '', $content);
        return $content;
    }
}