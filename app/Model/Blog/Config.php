<?php

declare (strict_types=1);
namespace App\Model\Blog;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $config_type 
 * @property string $config_name 
 * @property string $config_value 
 * @property string $create_at 
 * @property string $update_at 
 * @property int $create_by 
 * @property int $update_by 
 */
class Config extends Model
{
    const CREATED_AT = 'create_at';

    const UPDATED_AT = 'update_at';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'config';
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
    protected $fillable = ['id', 'config_type', 'config_name', 'config_value', 'create_at', 'update_at', 'create_by', 'update_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'create_by' => 'integer', 'update_by' => 'integer'];
}