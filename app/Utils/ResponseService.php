<?php
declare(strict_types = 1);

namespace App\Utils;

use App\Constants\ErrorCode;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface as Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Response 返回类
 *
 * @author yls
 * @package App\library
 */
class ResponseService
{
    /**
     * @Inject()
     * @var Response
     */
    private Response $response;

    /**
     * @var int|null 返回业务码
     */
    private ?int $code = null;

    /**
     * @var string|null 返回提示
     */
    private ?string $message = null;

    /**
     * @var array 返回的header头
     */
    private array $headers = [];

    /**
     * @var int http 返回码
     */
    private int $httpCode = 200;

    /**
     * 设置返回代码
     *
     * @author yls
     * @param int $code
     * @return $this
     */
    private function setCode(int $code) : self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * 设置返回语
     *
     * @author yls
     * @param string $message
     * @return $this
     */
    private function setMessage(string $message) : self
    {
        $this->message = $message;
        return $this;
    }


    /**
     * 设置header头部
     *
     * @author yls
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setHeader(string $name, string $value) : self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * 设置http返回码
     *
     * @author yls
     * @param int $httpCode
     * @return $this
     */
    public function setHttpCode(int $httpCode) : self
    {
        $this->httpCode = $httpCode;
        return $this;
    }

    /**
     * 成功返回
     *
     * @author yls
     * @param array       $data
     * @param int         $code
     * @param string|null $message
     * @return ResponseInterface
     */
    public function success(array $data = [], int $code = ErrorCode::SUCCESS, string $message = null) : ResponseInterface
    {
        return $this->response($code, $message, $data);
    }

    /**
     * 失败返回
     *
     * @author yls
     * @param int    $code
     * @param string $message
     * @return ResponseInterface
     */
    public function fail(int $code = ErrorCode::FAILED, string $message = null) : ResponseInterface
    {
        return $this->response($code, $message);
    }


    /**
     * 返回结构化数据
     *
     * @author yls
     * @param int         $code
     * @param string|null $message
     * @param array       $data
     * @return ResponseInterface
     */
    private function response(int $code, ?string $message, array $data = []) : ResponseInterface
    {
        $this->setCode($code);
        $this->setMessage(null === $message ? ErrorCode::getMessage($code) : $message);

        $response = $this->response->json($this->formatData($data))->withStatus($this->httpCode);
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }
        return $response;
    }

    /**
     * 格式化排版返回的数据
     *
     * @author yls
     * @param array $data
     * @return array
     */
    private function formatData(array $data) : array
    {
        return array_merge([
            'code'    => $this->code,
            'message' => $this->message,
        ], $data);
    }
}