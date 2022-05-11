<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $title 
 * @property int $chapter_id 
 * @property int $book_id 
 * @property int $sort 
 * @property int $wordnumber 
 * @property string $url 
 * @property int $is_oss 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class Article extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article';
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
        'is_oss' => [
            0 => '否',
            1 => '是',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'title', 'chapter_id', 'book_id', 'sort', 'wordnumber', 'url', 'is_oss', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'chapter_id' => 'integer', 'book_id' => 'integer', 'sort' => 'integer', 'wordnumber' => 'integer', 'is_oss' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}