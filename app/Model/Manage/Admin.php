<?php

declare (strict_types=1);
namespace App\Model\Manage;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $username 
 * @property string $password 
 * @property string $salt 
 * @property string $create_at 
 * @property string $update_at 
 * @property int $create_by 
 * @property int $update_by 
 */
class Admin extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin';
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
    protected $fillable = ['id', 'username', 'password', 'salt', 'create_at', 'update_at', 'create_by', 'update_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'create_by' => 'integer', 'update_by' => 'integer'];
}