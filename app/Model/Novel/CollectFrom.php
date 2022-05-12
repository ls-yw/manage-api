<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property int $book_id
 * @property int $collect_id 
 * @property int $from_article_id 
 * @property string $from_url 
 * @property int $from_sort 
 * @property string $from_title 
 * @property int $from_status 
 * @property string $url 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class CollectFrom extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collect_from';

    /**
     * @var string[][] 数据量字段的值所对应的中文名称
     */
    public static array $fieldsMappingName = [
        /**
         * @var string[] 连载状态
         */
        'from_status' => [
            0 => '未采集',
            1 => '已采集',
        ],
    ];
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'novel';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'book_id', 'collect_id', 'from_article_id', 'from_url', 'from_sort', 'from_title', 'from_status', 'url', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'from_book_id' => 'integer', 'collect_id' => 'integer', 'from_article_id' => 'integer', 'from_sort' => 'integer', 'from_status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}