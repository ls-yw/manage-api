<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $mobile 
 * @property string $username 
 * @property string $password 
 * @property string $salt 
 * @property string $last_ip 
 * @property string $last_time 
 * @property int $count 
 * @property int $is_vip 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';
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
    protected $fillable = ['id', 'mobile', 'username', 'password', 'salt', 'last_ip', 'last_time', 'count', 'is_vip', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'count' => 'integer', 'is_vip' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}