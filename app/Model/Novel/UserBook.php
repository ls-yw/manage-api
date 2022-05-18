<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
use Hyperf\Database\Model\Relations\HasOne;

/**
 * @property int $id 
 * @property int $uid 
 * @property int $book_id 
 * @property int $article_id 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class UserBook extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_book';
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
    protected $fillable = ['id', 'uid', 'book_id', 'article_id', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'book_id' => 'integer', 'article_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];

    public function book() : HasOne
    {
        return $this->hasOne(Book::class, 'id', 'book_id')->select(['id', 'name']);
    }

    public function article() : HasOne
    {
        return $this->hasOne(Article::class, 'id', 'article_id')->select(['id', 'title']);
    }
}