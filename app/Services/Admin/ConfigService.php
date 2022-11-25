<?php
declare(strict_types = 1);

namespace App\Services\Admin;

use App\Base\BaseService;
use App\Model\Manage\Config;

class ConfigService extends BaseService
{
    public function getAll() : object
    {
        return Config::all();
    }

    public function save(string $type, array $data)
    {
        foreach ($data as $key => $value) {
            $insertData = [
                'config_key' => $key,
                'config_value' => $value,
                'type' => $type
            ];
            $info = $this->getByTypeAndKey($type, $key);
            $insertData['id'] = (int)$info->id;
            $this->saveData($insertData, Config::make());
        }
    }

    public function getByTypeAndKey(string $type, string $key):object
    {
        return Config::where(['type' => $type, 'config_key' => $key])->first();
    }
}