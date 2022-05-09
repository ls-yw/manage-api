<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage(int $code)
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("系统错误，请联系管理员！")
     */
    const SERVER_ERROR = 500;

    /**
     * @Message("成功")
     */
    const SUCCESS = 0;

    /**
     * @Message("失败")
     */
    const FAILED = 1;

    /**
     * @Message("未登录")
     */
    const NO_LOGIN = 101;

    /**
     * @Message("token生成失败")
     */
    const CREATE_TOKEN = 201;


    /************************ FORM *****************************/
    /**
     * @Message("用户名不能为空")
     */
    const EMPTY_USERNAME = 1001;

    /**
     * @Message("请输入正确的用户名")
     */
    const USERNAME_FAILED = 1002;

    /**
     * @Message("密码不能为空")
     */
    const EMPTY_PASSWORD = 1003;

    /**
     * @Message("请输入正确的密码")
     */
    const PASSWORD_FAILED = 1004;





}
