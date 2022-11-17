<?php
declare(strict_types = 1);

namespace App\Base;

use App\Constants\ErrorCode;
use App\Exception\ManageException;
use App\Model\Model;
use App\Utils\Helper\HelperArray;
use App\Utils\Helper\HelperHttp;
use App\Utils\Log\Log;

class BaseService
{
    /**
     * 保存数据
     *
     * @author yls
     * @param array $data
     * @param Model $model
     * @return int
     */
    protected function saveData(array $data, Model $model) : int
    {
        $id = $data['id'] ?? 0;
        unset($data['id']);
        if (empty($id)) {
            return $model->insertGetId($data);
        } else {
            $info = $model->find((int) $id);
            if (empty($info)) {
                throw new ManageException(ErrorCode::NO_FOUND_DATA);
            }
            return $model->where('id', $id)->update($data);
        }
    }

    /**
     * 填充订单中字段的中文名称
     *
     * @author yls
     * @param object|array     $data
     * @param string $model 相关model的完全名称，如refund::class
     * @return object|array
     */
    public function fillNameToModel(object|array $data, string $model):object|array
    {
        if (empty($data)) {
            return $data;
        }
        if ($data instanceof Model) {
            foreach ($data->attributesToArray() as $key => $value) {
                if (isset($model::$fieldsMappingName[$key])) {
                    if (is_object($data)) {
                        $data->{$key.'_name'} = $model::$fieldsMappingName[$key][$value] ?? '未知';
                    }else {
                        $data[$key.'_name'] = $model::$fieldsMappingName[$key][$value] ?? '未知';
                    }
                }
            }
        }else {
            foreach ($data as $key => &$value) {
                if (is_numeric($key)) {
                    $value = $this->fillNameToModel($value, $model);

                }
            }
        }

        return $data;
    }

    /**
     * 上传远程图片
     *
     * @author yls
     * @param string $image
     * @return string
     */
    public function uploadRemoteImage(string $image):string
    {
        $url    = env('UPLOAD_URL') . '/upload/urlImg?project=novel&url=' . $image;
        $res    = (new HelperHttp())->get($url);
        $result = HelperArray::jsonDecode($res);
        if (!isset($result['code']) || 0 !== $result['code']) {
            Log::error('上传封面图片失败', 'collect');
            Log::error($url, 'collect');
            Log::error($res, 'collect');
            throw new ManageException(ErrorCode::UPLOAD_THUMB_IMG);
        }
        return env('UPLOAD_URL') . '/' . $result['url'];
    }
}