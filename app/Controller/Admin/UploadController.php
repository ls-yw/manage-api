<?php
declare(strict_types = 1);

namespace App\Controller\Admin;

use App\Base\BaseController;
use App\Constants\ErrorCode;
use App\Exception\ManageException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use woodlsy\httpClient\HttpCurl;

class UploadController extends BaseController
{
    public function index(RequestInterface $request) : ResponseInterface
    {
        $file = $request->file('file');
        $type = $request->query('type');

        // 该路径为上传文件的临时路径
        $path = $file->getPath();

        // 由于 Swoole 上传文件的 tmp_name 并没有保持文件原名，所以这个方法已重写为获取原文件名的后缀名
        $extension = $file->getExtension();

        $newPath = '/tmp/'.time().mt_rand(100, 999).'.'.$extension;
        $file->moveTo($newPath);

        $url  = env('UPLOAD_URL') . '/upload/img?project=' . $type;

        // 通过 isMoved(): bool 方法判断方法是否已移动
        if ($file->isMoved()) {
            $data = array('file'=>new \CURLFile(realpath($newPath), $file->getClientMediaType(), $file->getClientFilename()));
            $result = (new HttpCurl())->setUrl($url)->setData($data)->setKeepDataFormat(true)->post();
            $res = @json_decode($result, true);
            if(!isset($res['code'])){
                throw new ManageException(ErrorCode::UPLOAD_FAILEd);
            }
            if($res['code'] != 0){
                throw new \Exception($res['msg']);
            }
            @unlink($newPath);
            return $this->success(['data' => env('UPLOAD_URL').'/'.$res['url']]);
        } else {
            throw new ManageException(ErrorCode::UPLOAD_FAILEd);
        }
    }
}