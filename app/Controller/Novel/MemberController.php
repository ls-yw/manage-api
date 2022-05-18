<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Services\Novel\MemberService;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Ip2Region;
use Psr\Http\Message\ResponseInterface;

class MemberController extends BaseController
{
    /**
     * 列表
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function list(RequestInterface $request) : ResponseInterface
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        $data          = [];
        $data['list']  = (new MemberService())->getList($page, $size);
        $data['total'] = (new MemberService())->getListCount();

        if (!empty($data['list'])) {
            foreach ($data['list'] as $key => $value) {
                $ip2region = new Ip2Region();
                try {
                    $info = $ip2region->btreeSearch($value['last_ip']);
                    $data['list'][$key]['ip_region'] = preg_replace('/[0|]/i','',$info['region'] ?? '');
                }catch (Exception ) {
                    $data['list'][$key]['ip_region'] = '';
                }
            }
        }

        return $this->success($data);
    }

    /**
     * 会员书架列表
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function book(RequestInterface $request) : ResponseInterface
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        $data          = [];
        $data['list']  = (new MemberService())->getBookList($page, $size);
        $data['total'] = (new MemberService())->getBookListCount();

        return $this->success($data);
    }
}