<?php
declare(strict_types = 1);

namespace App\Utils\Helper;

use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Utils\Log\Log;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;

class HelperHttp
{
    /**
     * @Inject
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    private array $data = [];

    public function get($url):string
    {
        return $this->_fetch($url, 'GET');
    }

    public function post($url):string
    {
        return $this->_fetch($url, 'POST');
    }

    /**
     * TODO
     *
     * @author yls
     * @param array $data
     * @return $this
     */
    public function setData(array $data) : static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * TODO
     *
     * @author yls
     * @param $url
     * @param $method
     * @return string
     */
    private function _fetch($url, $method) : string
    {
        try {
            // $options 等同于 GuzzleHttp\Client 构造函数的 $config 参数
            $options = [];
            // $client 为协程化的 GuzzleHttp\Client 对象
            $client = $this->clientFactory->create($options);
            $resp   = $client->request($method, $url, [
                'headers'         => [
                    'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,sm;q=0.7',
                    'Accept-Encoding' => 'gzip'
                ],
                'decode_content'  => true,// 解密gzip
                'connect_timeout' => 10,
                'form_params' => $this->data
            ]);
            //响应状态码
            /*$http_status = $resp->getStatusCode();
            echo $http_status;*/
            //获取页面数据
            $content = $resp->getBody()->getContents();
            $iconv   = mb_detect_encoding($content, array("ASCII", "UTF-8", "GBK", "GB2312", "BIG5"));
            return mb_convert_encoding($content, 'UTF-8', $iconv);
        }catch (GuzzleException $e) {
            Log::error('接口请求失败', 'GuzzleHttp');
            Log::error($url, 'GuzzleHttp');
            Log::error($e->getMessage(), 'GuzzleHttp');
            throw new ManageException(ErrorCode::CURL_FAILED);
        }
    }
}