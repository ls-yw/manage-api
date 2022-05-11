<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $name 
 * @property int $category 
 * @property string $author 
 * @property string $intro 
 * @property string $thumb_img 
 * @property int $click 
 * @property int $monthclick 
 * @property int $weekclick 
 * @property int $dayclick 
 * @property int $recommend 
 * @property int $coll 
 * @property int $is_finished 
 * @property int $articlenum 
 * @property int $wordsnumber 
 * @property int $collect_id 
 * @property int $from_collect_book_id 
 * @property int $is_collect 
 * @property string $last_collect_at 
 * @property string $last_at 
 * @property int $is_recommend 
 * @property int $quality 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class Book extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'book';
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
        'is_finished' => [
            0 => '连载',
            1 => '完本',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'category', 'author', 'intro', 'thumb_img', 'click', 'monthclick', 'weekclick', 'dayclick', 'recommend', 'coll', 'is_finished', 'articlenum', 'wordsnumber', 'collect_id', 'from_collect_book_id', 'is_collect', 'last_collect_at', 'last_at', 'is_recommend', 'quality', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'category' => 'integer', 'click' => 'integer', 'monthclick' => 'integer', 'weekclick' => 'integer', 'dayclick' => 'integer', 'recommend' => 'integer', 'coll' => 'integer', 'is_finished' => 'integer', 'articlenum' => 'integer', 'wordsnumber' => 'integer', 'collect_id' => 'integer', 'from_collect_book_id' => 'integer', 'is_collect' => 'integer', 'is_recommend' => 'integer', 'quality' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}