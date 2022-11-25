<?php
declare(strict_types = 1);

namespace App\Controller\Admin;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Admin\ConfigService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ConfigController extends BaseController
{
    public function index() : ResponseInterface
    {
        $data         = [];
        $info = (new ConfigService())->getAll();
        foreach ($info as $value) {
            $data[$value['type']][$value['config_key']] = $value['config_value'];
        }
        return $this->success(['data' => $data]);
    }

    public function save(RequestInterface $request) : ResponseInterface
    {
        $type = (string)$request->input('type');
        $data = (array)$request->input('data');
        if (empty($type) || empty($data)) {
            throw new ManageException(ErrorCode::PARAMS_FAILED);
        }
        (new ConfigService())->save($type, $data);

        return $this->success();
    }
}