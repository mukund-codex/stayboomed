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
        'user_id', 'title', 'publish_start_date', 'publish_end_date', 'job_start_date','job_end_date', 'job_location', 'job_description', 'job_tags', 'vacancies', 'job_duration', 'gender', 'age_from','age_to', 'city_leaving', 'physical_attribute', 'experience', 'education', 'profession_id', 'subscription_type', 'budget_from', 'budget_to', 'budget_time', 'details', 'expertise', 'category', 'language', 'job_type', 'other_categories', 'audition_required', 'audition_script'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
     //protected $casts = [
    //    'id' => 'string'
    //];
    
    protected $casts = [
        'expertise'         => 'array',
        'category'          => 'array',
        'language'          => 'array',
        'job_type'          => 'array',
        'other_categories'  => 'array',
        'gender'            => 'array',
    ];

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
