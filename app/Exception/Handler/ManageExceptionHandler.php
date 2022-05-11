<?php
declare(strict_types = 1);

namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Exception\ManageException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ManageExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response) : ResponseInterface
    {
        // 判断被捕获到的异常是希望被捕获的异常
        if ($throwable instanceof ManageException || 'dev' === env('APP_ENV', 'prod')) {
            // 阻止异常冒泡
            $this->stopPropagation();

            // 格式化输出
            $data = json_encode([
                'code' => 0 === $throwable->getCode() ? ErrorCode::FAILED : $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
            return $response->withStatus(200)->withBody(new SwooleStream($data));
        }else {
            // 格式化输出
            $data = json_encode([
                'code' => ErrorCode::SERVER_ERROR,
                'message' => ErrorCode::getMessage(ErrorCode::SERVER_ERROR),
            ], JSON_UNESCAPED_UNICODE);

            // 系统报错，要记录日志 TODO

            // 系统报错，输出到控制面板
            $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $this->logger->error($throwable->getTraceAsString());
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }
    }

    /**
     * 判断该异常处理器是否要对该异常进行处理
     */
    public function isValid(Throwable $throwable) : bool
    {
        return true;
    }
}