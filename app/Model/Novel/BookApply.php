<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property int $uid 
 * @property string $name 
 * @property string $author 
 * @property string $platform 
 * @property string $reply 
 * @property int $book_id 
 * @property string $reply_at 
 * @property string $create_at 
 * @property string $update_at 
 * @property int $create_by 
 * @property int $update_by 
 */
class BookApply extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'book_apply';
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
    protected $fillable = ['id', 'uid', 'name', 'author', 'platform', 'reply', 'book_id', 'reply_at', 'create_at', 'update_at', 'create_by', 'update_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'book_id' => 'integer', 'create_by' => 'integer', 'update_by' => 'integer'];
}