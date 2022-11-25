<?php
declare(strict_types = 1);

namespace App\Utils\Middleware;

use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Utils\Helper\HelperArray;
use App\Utils\Redis\Redis;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginMiddleware implements MiddlewareInterface
{
    // 登录
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $token = $this->getToken($request);
        $urlPath = $request->getUri()->getPath();
        if ('/login' === $urlPath) {
            return $handler->handle($request);
        }
        // 临时代码 start
        if ('/novel/test' === $urlPath) {
            return $handler->handle($request);
        }
        // 临时代码 end
        if (empty($token)) {
            throw new ManageException(ErrorCode::NO_LOGIN);
        }
        $admin = Redis::getInstance()->get($token);
        if (empty($admin)) {
            throw new ManageException(ErrorCode::NO_LOGIN);
        }

        Context::set('admin', HelperArray::jsonDecode($admin));
        Context::set('token', $token);
        return $handler->handle($request);
    }

    /**
     * 获取token
     *
     * @author yls
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getToken(ServerRequestInterface $request) : string
    {
        $tokens = $request->getHeader('token');
        return empty($tokens) ? '' : current($tokens);
    }
}