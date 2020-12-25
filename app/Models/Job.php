<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidTrait;

class Job extends Model
{
    //
    use HasFactory;

    use SoftDeletes;  

    public $incrementing = 'true';

    protected $table = "new_jobs";

    protected $primary_key = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'publish_date', 'end_date', 'job_location', 'job_description', 'job_tags', 'vacancies', 'job_duration', 'gender', 'age', 'city_leaving', 'language', 'physical_attribute', 'experience', 'education', 'profession_id', 'subscription_type', 'budget', 'budget_time', 'details'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
     //protected $casts = [
    //    'id' => 'string'
    //];
    
    public function profession() {
        return $this->belongsTo(Profession::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function appliedJobs() {
        return $this->hasMany(AppliedJobs::class);
    }
}
