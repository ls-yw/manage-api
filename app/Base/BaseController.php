<?php
declare(strict_types = 1);

namespace App\Base;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Utils\ResponseService;

/**
 * Class BaseController
 *
 * @author yls
 * @package App\Base
 * @method success(array $data = [], int $code = ErrorCode::SUCCESS, string $message = null)
 * @method fail(int $code = ErrorCode::FAILED, string $message = null);
 * @method setHeader(string $name, string $value)
 * @method setHttpCode(int $httpCode)
 */
class BaseController extends AbstractController
{
    /**
     *
     * @author yls
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists(ResponseService::class, $name)) {
            return make(ResponseService::class)->{$name}(...$arguments);
        } else {
            return null;
        }
    }
}