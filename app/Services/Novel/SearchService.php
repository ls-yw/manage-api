<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Model\Novel\Search;
use App\Model\Novel\Spider;

class SearchService extends BaseService
{
    /**
     * 搜索列表
     *
     * @author yls
     * @param int $page
     * @param int $row
     * @return object
     */
    public function getList(string $sort, int $page, int $row): object
    {
        $sortArr = explode('-', $sort);
        $offset = ($page - 1) * $row;
        return Search::orderBy(end($sortArr), count($sortArr) === 2 ? 'desc' : 'asc')->offset($offset)->limit($row)->get();
    }

    /**
     * 搜索列表条数
     *
     * @author yls
     * @return int
     */
    public function getListCount():int
    {
        return Search::orderBy('id', 'desc')->count();
    }

    /**
     * 搜索来源列表
     *
     * @author yls
     * @param int $page
     * @param int $row
     * @return object
     */
    public function getSpiderList(int $page, int $row):object
    {
        $offset = ($page - 1) * $row;
        return Spider::orderBy('id', 'desc')->offset($offset)->limit($row)->get();
    }

    /**
     * 搜索来源列表条数
     *
     * @author yls
     * @return int
     */
    public function getSpiderListCount():int
    {
        return Search::orderBy('id', 'desc')->count();
    }
}