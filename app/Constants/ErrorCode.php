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
     * @Message("保存失败")
     */
    const SAVE_FAILED = 2;

    /**
     * @Message("删除失败")
     */
    const DELETE_FAILED = 3;

    /**
     * @Message("变更失败")
     */
    const CHANGE_FAILED = 4;

    /**
     * @Message("确认失败")
     */
    const CONFIRM_FAILED = 5;


    /**
     * @Message("curl请求失败")
     */
    const CURL_FAILED = 10;

    /**
     * @Message("未登录")
     */
    const NO_LOGIN = 101;

    /**
     * @Message("token生成失败")
     */
    const CREATE_TOKEN = 201;
    /**
     * @Message("参数错误")
     */
    const PARAMS_FAILED = 202;

    const ERROR_CUSTOM = 888;



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
    /**
     * @Message("名称不能为空")
     */
    const EMPTY_NAME = 1005;
    /**
     * @Message("封面缩略图不能为空")
     */
    const EMPTY_THUMB_IMG = 1006;

    /**
     * @Message("上传失败")
     */
    const UPLOAD_FAILEd = 1007;
    /**
     * @Message("请输入搜索关键字")
     */
    const EMPTY_KEYWORD = 1008;

    /************************ MODEL *****************************/
    /**
     * @Message("找不到数据")
     */
    const NO_FOUND_DATA = 1101;


    /************************ NOVEL *****************************/

    /**
     * @Message("该小说章节未清空，不能删除小说")
     */
    const DELETE_BOOK_HAVE_ARTICLE = 12001;

    /**
     * @Message("获取小说内容失败")
     */
    const GET_CONTENT_FAILED = 12002;
    /**
     * @Message("文章内容不能为空")
     */
    const EMPTY_ARTICLE_CONTENT = 12003;
    /**
     * @Message("oss写入失败")
     */
    const WRITE_OSS_OF_CONTENT = 12004;
    /**
     * @Message("删除oss章节失败")
     */
    const DELETE_OSS_ARTICLE = 12005;
    /**
     * @Message("不存在采集规则")
     */
    const NO_EXISTS_COLLECT_RULES = 12006;

    /**
     * @Message("该小说已存在但不是该采集节点采集，如需重新采集，请先删除该小说")
     */
    const COLLECT_ID_NO_EQ = 12007;
    /**
     * @Message("上传封面图片失败")
     */
    const UPLOAD_THUMB_IMG = 12008;
    /**
     * @Message("该小说已采集，请去采集小说里面继续采集")
     */
    const COLLECT_ID_EQ = 12009;

    /*****APP 采集******/
    /**
     * @Message("采集源不存在")
     */
    const APP_COLLECT_SOURCE_NO_EXITS = 12100;



}
