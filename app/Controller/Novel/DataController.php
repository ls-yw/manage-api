<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Services\Novel\SearchService;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Ip2Region;
use Psr\Http\Message\ResponseInterface;

class DataController extends BaseController
{
    /**
     * 搜索关键字列表
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function search(RequestInterface $request) : ResponseInterface
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);
        $sort = (string)$request->query('sort', '-id');

        $data          = [];
        $data['list']  = (new SearchService())->getList($sort, $page, $size);
        $data['total'] = (new SearchService())->getListCount();

        return $this->success($data);
    }

    /**
     * 搜素引擎列表
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function searchSpider(RequestInterface $request) : ResponseInterface
    {
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        $data          = [];
        $data['list']  = (new SearchService())->getSpiderList($page, $size);
        $data['total'] = (new SearchService())->getSpiderListCount();

        if (!empty($data['list'])) {
            foreach ($data['list'] as $key => $value) {
                $ip2region = new Ip2Region();
                try {
                    $info = $ip2region->btreeSearch($value['ip']);
                    $data['list'][$key]['ip_region'] = preg_replace('/[0|]/i','',$info['region'] ?? '');
                }catch (Exception ) {
                    $data['list'][$key]['ip_region'] = '';
                }
            }
        }

        return $this->success($data);
    }
}