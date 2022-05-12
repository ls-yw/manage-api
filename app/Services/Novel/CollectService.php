<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Model\Novel\Collect;
use App\Model\Novel\CollectFrom;

class CollectService extends BaseService
{
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
        $list   = Collect::orderBy('id', 'desc')->offset($offset)->limit($size)->get();
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
        return Collect::count();
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
    public function getCollectFormList(int $bookId, int $status, int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        $list = CollectFrom::where(['book_id' => $bookId, 'from_status' => $status])->offset($offset)->limit($size)->get();
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
    public function getCollectFormListCount(int $bookId, int $status) : int
    {
        return CollectFrom::where(['book_id' => $bookId, 'from_status' => $status])->count();
    }

    /**
     * 确认采集ID
     *
     * @author yls
     * @param array|int $ids
     * @return int
     */
    public function confirmCollect(array|int $ids):int
    {
        return CollectFrom::where('id', $ids)->update(['from_status'=>1]);
    }
}