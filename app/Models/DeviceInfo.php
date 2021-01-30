<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidTrait;

class DeviceInfo extends Model
{
    use SoftDeletes, UuidTrait;

    protected $table = 'device_info';

    /**
    * Indicates if the IDs are auto-incrementing.
    *
    * @var bool
    */
    public $incrementing = false;
    
    protected $primaryKey = "id";

    protected $keyType = "string";

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'device_info' => 'array',
    ];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'user_id', 'device_id', 'device_info'
    ];

    /**
     * Get User where this Device Info Belongs
     */
    public function user() {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
