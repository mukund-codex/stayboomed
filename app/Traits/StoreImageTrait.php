<?php   

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 *  Store Image Trait
 */
trait StoreImageTrait
{
    /**
    * Does very basic image validity checking and stores it. Redirects back if somethings wrong.
    * @Notice: This is not an alternative to the model validation for this field.
    *
    * @param Request $request
    * @return $this|false|string
    */
    public function verifyAndStoreImage( Request $request, $fieldname = 'image', $directory = 'unknown' ) {
        if( $request->hasFile( $fieldname ) ) {
            if (!$request->file($fieldname)->isValid()) {
                dd('in valid');
            }
            
            // return Storage::disk('public')->put('filename', $request->file($fieldname));
            return $request->file($fieldname)->store('image/' . $directory, 'public');
        }
        return null;
    }
}
