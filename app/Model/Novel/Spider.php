<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $host 
 * @property string $keyword 
 * @property string $param 
 * @property string $domain 
 * @property string $ip 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class Spider extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spider';
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
    protected $fillable = ['id', 'host', 'keyword', 'param', 'domain', 'ip', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}