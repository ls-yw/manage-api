<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $name 
 * @property string $ename
 * @property string $host
 * @property string $iconv 
 * @property int $collect_status 
 * @property int $is_deleted 
 * @property int $target_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class Collect extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collect';
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'novel';

    /**
     * @var string[][] 数据量字段的值所对应的中文名称
     */
    public static array $fieldsMappingName = [
        /**
         * @var string[] 连载状态
         */
        'collect_status' => [
            0 => '不可采集',
            1 => '可采集',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'ename', 'host', 'iconv', 'collect_status', 'is_deleted', 'target_type', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'collect_status' => 'integer', 'is_deleted' => 'integer', 'target_type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}