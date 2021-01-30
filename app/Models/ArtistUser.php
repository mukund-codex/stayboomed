<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidTrait;
use Laravel\Passport\HasApiTokens;

class ArtistUser extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable, HasFactory, SoftDeletes;  

    public $incrementing = 'true';

    protected $table = "users";

    protected $primary_key = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fullname', 'designation', 'username', 'email', 'user_type', 'password', 'number', 'state_id', 'city_id', 'gender', 'profession_id', 'dob' , 'address', 'referral_code'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
     //protected $casts = [
    //    'id' => 'string'
    //];

    public function state() {
        return $this->belongsTo(State::class, 'state_id', 'id');
    } 

    public function city() {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function profession() {
        return $this->belongsTo(Profession::class, 'profession_id', 'id');
    }

    public function providerDetails() {
        return $this->hasOne(ProviderDetails::class);
    }

    public function artistProfession() {
        return $this->hasMany(ArtistProfession::class);
    }
}
