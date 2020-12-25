<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidTrait;

class ArtistDetails extends Model
{
    //
    use HasFactory;

    use SoftDeletes;  

    public $incrementing = 'true';

    protected $table = "artist_details";

    protected $primary_key = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'country', 'zip_code', 'corresponding_address', 'permanent_address', 'profile_picture', 'cover_picture'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
     //protected $casts = [
    //    'id' => 'string'
    //];
    
    public function User() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
