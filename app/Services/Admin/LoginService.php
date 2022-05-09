<?php
declare(strict_types = 1);

namespace App\Services\Admin;

use App\Base\BaseService;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Manage\Admin;
use App\Utils\Helper\HelperArray;
use App\Utils\Helper\HelperString;
use App\Utils\Redis\Redis;

class LoginService extends BaseService
{
    /**
     * 去登录
     *
     * @author yls
     * @param string $username
     * @param string $password
     * @return string
     */
    public function toLogin(string $username, string $password) : string
    {
        $admin = Admin::where(['username' => $username])->first();
        if (empty($admin)) {
            throw new ManageException(ErrorCode::USERNAME_FAILED);
        }

        $verifyPassword = crypt(md5($password), $admin['salt']);
        if ($verifyPassword !== $admin['password']) {
            throw new ManageException(ErrorCode::PASSWORD_FAILED);
        }
        $token = HelperString::createToken((string) $admin['id']);
        $res   = Redis::getInstance()->setex($token, 86400, HelperArray::jsonEncode($admin));
        if (!$res) {
            throw new ManageException(ErrorCode::CREATE_TOKEN);
        }
        return $token;
    }
}