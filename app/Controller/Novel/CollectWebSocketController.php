<?php
declare(strict_types = 1);

namespace App\Controller\Novel;

use App\Services\Novel\CollectService;
use App\Utils\Helper\HelperArray;
use App\Utils\Redis\Redis;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Sender;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

class CollectWebSocketController implements OnMessageInterface, OnCloseInterface,OnOpenInterface
{
    /**
     * @Inject
     * @var Sender
     */
    protected Sender $sender;

    protected array $admin;

    public function onMessage($server, Frame $frame) : void
    {
        $data = HelperArray::jsonDecode($frame->data);
        if (empty($data)) {
            $server->push($frame->fd, HelperArray::jsonEncode(['code' => 1, 'message' => '发送的数据格式不正确']));
            return;
        }
        $action = $data['action'] ?? '';
        if (empty($action)) {
            $server->push($frame->fd, HelperArray::jsonEncode(['code' => 1, 'message' => '发送的数据参数不正确']));
            return;
        }
        if (!method_exists($this, $action)) {
            $server->push($frame->fd, HelperArray::jsonEncode(['code' => 1, 'message' => '错误的方法']));
            return;
        }
        if ('checkLogin' !== $action && empty($this->admin)) {
            $server->push($frame->fd, HelperArray::jsonEncode(['code' => 1, 'message' => '未登录，请先登录']));
            return;
        }
        $this->{$action}($frame, $data);
    }

    public function onClose($server, int $fd, int $reactorId) : void
    {
        var_dump('closed');
    }
    public function onOpen($server, Request $request) : void
    {
        var_dump('websocket open');
    }

    /**
     * 检测登录
     *
     * @author yls
     * @param Frame $frame
     * @param array $data
     */
    public function checkLogin(Frame $frame, array $data) : void
    {
        $token = $data['token'] ?? '';
        if (empty($token)) {
            $this->sender->push($frame->fd, HelperArray::jsonEncode(['code' => 1, 'message' => 'token不存在']));
            return;
        }
        $admin = Redis::getInstance()->get($token);
        if (empty($admin)) {
            $this->sender->push($frame->fd, HelperArray::jsonEncode(['code' => 1, 'message' => '未登录或已失效，请重新登录']));
            return;
        }
        $this->admin = HelperArray::jsonDecode($admin);
        $this->sender->push($frame->fd, HelperArray::jsonEncode(['code' => 200, 'message' => '登录成功']));
    }

    /**
     * 采集章节
     *
     * @author yls
     * @param Frame $frame
     * @param array $data
     */
    public function collectArticle(Frame $frame, array $data)
    {
        $bookId = (int)($data['bookId'] ?? 0);
        if (empty($bookId)) {
            $this->sender->push($frame->fd, HelperArray::jsonEncode(['code' => 0, 'message' => '待采集的小说ID不能为空', 'class'=>'red']));
            return;
        }

        (new CollectService())->startCollect($bookId, $frame);
    }
}