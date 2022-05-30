<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property int $collect_id 
 * @property string $book_url 
 * @property int $book_id 
 * @property string $sub_book_id 
 * @property string $chapter_url 
 * @property string $article_url 
 * @property string $name
 * @property string $author 
 * @property string $intro 
 * @property string $thumb_img 
 * @property string $filter_thumb_img
 * @property string $finished 
 * @property string $category 
 * @property string $article_id 
 * @property string $article_title 
 * @property string $content 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class CollectRule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collect_rule';
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
    protected $fillable = ['id', 'collect_id', 'book_url', 'book_id', 'sub_book_id', 'chapter_url', 'article_url', 'name', 'author', 'intro', 'thumb_img', 'filter_thumb_img', 'finished', 'category', 'article_id', 'article_title', 'content', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'collect_id' => 'integer', 'book_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}