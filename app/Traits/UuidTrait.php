<?php   

namespace App\Traits;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;


/**
 * Generate UUID
 */
trait UuidTrait
{
    /**
    * Generate UUID v4 when creating model.
    */
    protected static function boot()
    {
        parent::boot();
        self::uuid();
    }

    /**
    * Defines the UUID field for the model.
    * @return string
    */
    protected static function uuidField()
    {
        return 'id';
    }

    /**
    * Use if boot() is overridden in the model.
    */
    protected static function uuid()
    {
        static::creating(function ($model) {
            $model->{self::uuidField()} = (string) Str::uuid();//Uuid::uuid4()->toString();
        });
    }
}


?>