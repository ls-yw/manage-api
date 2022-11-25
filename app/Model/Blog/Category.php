<?php

declare (strict_types=1);
namespace App\Model\Blog;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $name 
 * @property int $pid 
 * @property int $is_deleted 
 * @property string $create_at 
 * @property string $update_at 
 * @property int $create_by 
 * @property int $update_by 
 */
class Category extends Model
{
    const CREATED_AT = 'create_at';

    const UPDATED_AT = 'update_at';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'category';
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
    protected $fillable = ['id', 'name', 'pid', 'is_deleted', 'create_at', 'update_at', 'create_by', 'update_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'is_deleted' => 'integer', 'create_by' => 'integer', 'update_by' => 'integer'];
}