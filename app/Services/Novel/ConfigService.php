<?php
declare(strict_types = 1);

namespace App\Services\Novel;

use App\Base\BaseService;
use App\Model\Novel\Config;
use App\Utils\Helper\HelperArray;

class ConfigService extends BaseService
{
    /**
     * 获取配置键值对
     *
     * @author yls
     * @param string $type
     * @return array
     */
    public function getPairs(string $type):array
    {
        return HelperArray::getPairs(Config::where('type', $type)->get()->toArray(), 'config_key', 'config_value');
    }

    /**
     * 更新配置
     *
     * @author yls
     * @param string $type
     * @param array  $data
     * @return bool
     */
    public function save(string $type, array $data): bool
    {
        $insertData = [];
        foreach ($data as $key => $value) {
            $insertData[] = ['config_value' => $value, 'type' => $type, 'config_key' => $key];
        }
        return Config::insert($insertData);
    }
}