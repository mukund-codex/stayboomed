<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidTrait;

class Subscription extends Model
{
    //
    use HasFactory;

    use SoftDeletes;  

    public $incrementing = 'true';

    protected $table = "subscription";

    protected $primary_key = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'price', 'job_apply', 'subscription_type'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
     //protected $casts = [
    //    'id' => 'string'
    //];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
