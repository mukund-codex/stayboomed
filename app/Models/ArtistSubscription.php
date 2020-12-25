<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidTrait;

class ArtistSubscription extends Model
{
    //

    use HasFactory;

    use SoftDeletes;  

    public $incrementing = 'true';

    protected $table = "artist_subscription";

    protected $primary_key = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_id', 'user_id'
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
        return $this->belongsTo(Users::class);
    }

    public function subscription() {
        return $this->belongsTo(Subscription::class);
    }
}
