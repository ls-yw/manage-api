<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Novel\ConfigService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SettingController extends BaseController
{
    /**
     * 获取配置
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function index(RequestInterface $request) : ResponseInterface
    {
        $data = (new ConfigService())->getPairs('system');

        return $this->success(['data' => $data]);
    }

    /**
     * 保存配置
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function save(RequestInterface $request):ResponseInterface
    {
        $data = [
            'host'                 => $request->input('host'),
            'm_host'               => $request->input('m_host'),
            'host_name'            => $request->input('host_name'),
            'host_seo_name'        => $request->input('host_seo_name'),
            'host_seo_keywords'    => $request->input('host_seo_keywords'),
            'host_seo_description' => $request->input('host_seo_description'),
            'powerby'              => $request->input('powerby'),
            'record'               => $request->input('record'),
            'notice'               => $request->input('notice'),
        ];

        $row = (new ConfigService())->save('system', $data);
        if (!$row) {
            throw new ManageException(ErrorCode::SAVE_FAILED);
        }
        return $this->success();
    }
}