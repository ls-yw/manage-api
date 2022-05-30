<?php

declare (strict_types=1);
namespace App\Model\Novel;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $keyword 
 * @property string $platform 
 * @property int $num 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $created_by 
 * @property int $updated_by 
 */
class Search extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'search';
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
    protected $fillable = ['id', 'keyword', 'platform', 'num', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'num' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer'];
}