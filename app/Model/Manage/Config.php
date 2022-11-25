<?php

declare (strict_types=1);
namespace App\Model\Manage;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $type 
 * @property string $config_key 
 * @property string $config_value 
 * @property string $created_at
 * @property string $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class Config extends Model
{
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
    protected $connection = 'manage';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'type', 'config_key', 'config_value', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer'];
}