<?php

declare (strict_types=1);
namespace App\Model\Blog;

use App\Model\Model;
/**
 * @property int $id 
 * @property int $category_id 
 * @property string $title 
 * @property string $desc 
 * @property string $content 
 * @property string $img_url 
 * @property int $open_comment 
 * @property int $comment_num 
 * @property int $likes 
 * @property int $clicks 
 * @property string $tags 
 * @property int $is_deleted 
 * @property int $is_push 
 * @property int $sort 
 * @property string $fixed_time 
 * @property string $create_at 
 * @property string $update_at 
 * @property int $create_by 
 * @property int $update_by 
 */
class Article extends Model
{
    const CREATED_AT = 'create_at';

    const UPDATED_AT = 'update_at';

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
    protected $connection = 'blog';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'category_id', 'title', 'desc', 'content', 'img_url', 'open_comment', 'comment_num', 'likes', 'clicks', 'tags', 'is_deleted', 'is_push', 'sort', 'fixed_time', 'create_at', 'update_at', 'create_by', 'update_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'category_id' => 'integer', 'open_comment' => 'integer', 'comment_num' => 'integer', 'likes' => 'integer', 'clicks' => 'integer', 'is_deleted' => 'integer', 'is_push' => 'integer', 'sort' => 'integer', 'create_by' => 'integer', 'update_by' => 'integer'];
}