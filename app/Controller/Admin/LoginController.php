<?php
declare(strict_types = 1);

namespace App\Controller\Admin;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Services\Admin\LoginService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController extends BaseController
{
    /**
     * 登录
     *
     * @author yls
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function login(RequestInterface $request) : ResponseInterface
    {
        $username = (string) $request->input('username');
        $password = (string) $request->input('password');

        if (empty($username)) {
            throw new ManageException(ErrorCode::EMPTY_USERNAME);
        }
        if (strlen($username) < 5) {
            throw new ManageException(ErrorCode::USERNAME_FAILED);
        }

        if (empty($password)) {
            throw new ManageException(ErrorCode::EMPTY_PASSWORD);
        }

        if (strlen($password) < 6 || strlen($password) > 32) {
            throw new ManageException(ErrorCode::PASSWORD_FAILED);
        }

        $token = (new LoginService())->toLogin($username, $password);

        return $this->success(['data' => $token]);
    }
}