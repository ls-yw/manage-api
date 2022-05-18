<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Model\Novel\User;
use App\Model\Novel\UserBook;

class MemberService extends BaseService
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
        return User::orderBy('id', 'desc')->offset($offset)->limit($size)->get();
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @return int
     */
    public function getListCount() : int
    {
        return User::count();
    }

    /**
     * 获取列表
     *
     * @author yls
     * @param int $page
     * @param int $size
     * @return object
     */
    public function getBookList(int $page, int $size) : object
    {
        $offset = ($page - 1) * $size;
        return UserBook::with('book')->with('article')->orderBy('id', 'desc')->offset($offset)->limit($size)->get();
    }

    /**
     * 获取列表条数
     *
     * @author yls
     * @return int
     */
    public function getBookListCount() : int
    {
        return UserBook::count();
    }
}