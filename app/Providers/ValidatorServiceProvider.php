<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\{Arr, Str};
use Ramsey\Uuid\Uuid;
use DB;

class ValidatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('keywords', function ($attribute, $value, $parameters, $validator)
        {
            // put keywords into array
            $keywords = explode(',', $value);

            foreach($keywords as $keyword)
            {
                // do validation logic
                if(strlen($keyword) < 3)
                {
                    return false;
                }
            }

            return true;
        });

        Validator::extend('module_language', function ($attribute, $value, $parameters, $validator)
        {
            $language_id = $value;
            $module_id = Arr::get($validator->getData(), $parameters[0], null);

            if(!$module_id || !$language_id) { return false; }
            
            $is_module_language_exist = \App\Models\Module::with(['languages' => function($q) use($module_id, $language_id) {
                                            $q->where('language_id', '=', $language_id);
                                        }])
                                        ->where(['id' => $module_id])
                                        ->count();
            
            if( (int)$is_module_language_exist > 0) {
                return true;
            }

            return false;
        });

        Validator::replacer('module_language', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'language does not exist for module',$message);
        });

        Validator::extend('camp_module', function ($attribute, $value, $parameters, $validator)
        {
            $module_id = $value;
            $camp_id = Arr::get($validator->getData(), $parameters[0], null);

            if(!$camp_id || !$module_id) { return false; }            
            
            $is_camp_module_exist = \App\Models\Camp::with(['modules' => function($q) use($camp_id, $module_id) {
                                            $q->where('module_id', '=', $module_id);
                                        }])
                                        ->where(['id' => $camp_id])
                                        ->count();
            
            if( (int)$is_camp_module_exist <= 0) {
                return false;
            }
            
            return true;
        });

        Validator::replacer('camp_module', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'module does not exist for camp',$message);
        });

       Validator::extend('is_unique_geography', function ($attribute, $value, $parameters, $validator)
        {
            list($input_company, $input_campaign, $input_type, $input_parent) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);
            $input_type_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_type, null);
            $input_parent_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_parent, null);
            $geography_name = trim($value);
            
            $input_company_name = trim($input_company_name);
            $input_campaign_name = trim($input_campaign_name);
            $input_type_name = trim($input_type_name);
            $input_parent_name = trim($input_parent_name);
            

            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }
                      
            $campaign_id = $campaign->id;

            if(! $campaign->geo_master) { return false; }
            $geo_master = $campaign->geo_master;

            $geo_master_list = array_column($geo_master, 'id');

            // IF TYPE DOES NOT EXISTS IN GEOGRAPHY MASTER
            $geo_master_input = \App\Models\GeographyMaster::where(['name' => $input_type_name])->first();
            if(\is_null($geo_master_input)) { return false; }
            if(!in_array($geo_master_input->id, $geo_master_list)) { return false; }
            // END

            // GET IMMEDIATE PARENT TYPE FROM THE $geo_master_input OF TYPE PROVIDED
            $current_geo_master_index = \array_search($geo_master_input->id, $geo_master_list);
            $prev_index = $current_geo_master_index - 1;
            $prev_geo_master_id = ($prev_index >= 0 ) ? $geo_master_list[$prev_index] : NULL;
            // END
            
            if(is_null($prev_geo_master_id)) {                
                return !$this->duplicate_geography_check($campaign_id, $geography_name, $geo_master_input->id);
            } else {
                $is_parent_exist = \App\Models\Geography::where([
                    'type' => $prev_geo_master_id, 
                    'campaign_id' => $campaign_id,
                    'name' => $input_parent_name
                ])->first();
                    
                if(is_null($is_parent_exist)) { 
                    return false; 
                } else {
                    return !$this->duplicate_geography_check($campaign_id, $geography_name, $geo_master_input->id, $is_parent_exist->id);
                }
            }
        });

        Validator::replacer('is_unique_geography', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'geography name already taken',$message);
        });


        Validator::extend('is_campaign_of_company', function ($attribute, $value, $parameters, $validator)
        {
            list($input_company) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = $value;       
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }

            return true;
        });

        Validator::replacer('is_campaign_of_company', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'campaign does not belongs to company',$message);
        });

Validator::extend('is_unique_user_geography', function ($attribute, $value, $parameters, $validator)
        {
            list($input_company, $input_campaign, $input_type) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);
            $input_type_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_type, null);
            $geography_name = $value;            
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }
                        
            $campaign_id = $campaign->id;

            if(empty($campaign->geo_master)) { return false; }
            
            $geo_master_list = array_column($campaign->geo_master, 'id');

            // IF TYPE DOES NOT EXISTS IN GEOGRAPHY MASTER
            $geo_master_input = \App\Models\GeographyMaster::where(['name' => $input_type_name])->first();
            if(\is_null($geo_master_input)) { return false; }
            if(!in_array($geo_master_input->id, $geo_master_list)) { return false; }
            // END

            $geo_master_id = $geo_master_input->id;

            // IS GEOGRAPHY EXIST
            $is_geography_exist = \App\Models\Geography::where([
                'type' => $geo_master_id, 
                'campaign_id' => $campaign_id,
                'name' => $geography_name
            ])->first();

            if(is_null($is_geography_exist)) { 
                return false; 
            }
            // END

            $geography_id = $is_geography_exist->id;

            // IS USER GEOGRAPHY EXIST
            $is_geography_exist = \App\Models\UserGeography::where([
                'geography_id' => $geography_id,
            ])->first();

            if($is_geography_exist) { 
                return false; 
            }
            // END

            return true;
        });

        Validator::replacer('is_unique_user_geography', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'invalid user to geography row',$message);
        });

        Validator::extend('is_unique_user_mobile', function ($attribute, $value, $parameters, $validator)
        {
            list($input_company, $input_campaign) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);
            $input_user_mobile = $value;
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }

            $user = \App\Models\User::where(['campaign_id' => $campaign->id, 'mobile' => $input_user_mobile])->first();
            if($user) { return false; }

            return true;
        });

        Validator::replacer('is_unique_user_mobile', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'mobile already exist',$message);
        });

Validator::extend('is_unique_user_username', function ($attribute, $value, $parameters, $validator)
        {
            list($input_company, $input_campaign) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);
            $input_user_username = $value;
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }

            $user = \App\Models\User::where(['campaign_id' => $campaign->id, 'username' => $input_user_username])->first();
            if($user) { return false; }

            return true;
        });

        Validator::replacer('is_unique_user_username', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'username already exist',$message);
        });
        Validator::extend('is_unique_user_email', function ($attribute, $value, $parameters, $validator)
        {
            list($input_company, $input_campaign) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);
            $input_user_email = $value;
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }

            $user = \App\Models\User::where(['campaign_id' => $campaign->id, 'email_id' => $input_user_email])->first();
            if($user) { return false; }

            return true;
        });

        Validator::replacer('is_unique_user_email', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'email already exist',$message);
        });
        
        Validator::extend('check_doctor_form_keys', function ($attribute, $value, $parameters, $validator)
        {
            $camp_id = Arr::get($validator->getData(), $parameters[0], null);

            if(!$camp_id) { return false; }
            $form = $value;
            
            $doctor_master = \App\Models\DoctorMaster::where(['camp_id' => $camp_id])->first()->settings;
            if(!$doctor_master) {
                return false;
            }

            $doctor_master = json_decode($doctor_master);
            $is_all_keys_available = array_diff(array_column($doctor_master, 'name'), \array_keys($form));

            if(count($is_all_keys_available) > 0) { return false; }

            return true;
        });

        Validator::replacer('check_doctor_form_keys', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Invalid doctor form',$message);
        });

        Validator::extend('base64', function ($attribute, $value, $parameters, $validator) {
            if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $value)) {
                return true;
            } else {
                return false;
            }
        });

        Validator::extend('base64image', function ($attribute, $value, $parameters, $validator) {
            $explode = explode(',', $value);
            $allow = ['png', 'jpg'];
            /* $format = str_replace(
                [
                    'data:image/',
                    ';',
                    'base64',
                ],
                [
                    '', '', '',
                ],
                $explode[0]
            ); */
            // check file format
            
            /* if (!in_array($format, $allow)) {
                return false;
            } */
            // check base64 format
            if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[0])) {
                return false;
            }
            return true;
        });

        Validator::replacer('base64image', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Invalid Image',$message);
        });

        Validator::extend('isImageUrl', function ($attribute, $value, $parameters, $validator) {
            $url = $value;
            $url_headers = get_headers($url, 1);

            if(isset($url_headers['Content-Type'])){

                $type = strtolower($url_headers['Content-Type']);

                $valid_image_type = [
                    'image/png','image/jpg','image/jpeg','image/jpe',
                    'image/gif','image/tif','image/tiff','image/svg',
                    'image/ico','image/icon'
                ];

                if(! in_array($type, $valid_image_type)) {
                    return false;
                }

                if(isset($parameters) && is_array($parameters)) {
                    $extension = explode('/', $type )[1];

                    if(! in_array($extension, $parameters)) {
                        return false;
                    }
                }

                return true;
            }
            
            return false;
        });

        Validator::replacer('isImageUrl', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Invalid Image Url ',$message);
        });

        Validator::extend('is_valid_url', function ($attribute, $value, $parameters, $validator) {
            return filter_var($value, FILTER_VALIDATE_URL);
        });

        Validator::replacer('is_valid_url', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Invalid Url ',$message);
        });

        Validator::extend('is_file_exist', function ($attribute, $value, $parameters, $validator) {
            
            // dd($attribute, $value, $parameters);
            return true;
        });

        Validator::replacer('is_file_exist', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'File Required ',$message);
        });

        Validator::extend('is_form_field_exists', function ($attribute, $value, $parameters, $validator) {
            $type = $parameters[0];
            $separator = '.';
            $attributeKeys = explode($separator, $attribute);

            $adjacentInputIdKey = str_replace('*', $attributeKeys[1], $type);
            $adjacentInputIdValue = Arr::get($validator->getData(), $adjacentInputIdKey, null);

            if(!$adjacentInputIdKey || !$adjacentInputIdValue) { return false; }
           
            if(!$adjacentInputIdValue) { return false; }
            
            $form = \App\Models\FormMaster::find($adjacentInputIdValue);
            
            if(! $form) { return false; }

            $formData = $form->form;

            $formFieldCollection = array_column($formData, 'name');
            
            if(! $formFieldCollection) { return false; }

            if(! \in_array($value, $formFieldCollection)) { return false; }

            return true;
        });

        Validator::replacer('is_form_field_exists', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Invalid Form Field',$message);
        });

        Validator::extend('is_static_text', function ($attribute, $value, $parameters, $validator) {
            $type = $parameters[0];
            $input_id = $parameters[1];
            $separator = '.';
            $attributeKeys = explode($separator, $attribute);
            dd($attributeKeys);
            $adjacentInputIdKey = str_replace('*', $attributeKeys[1], $type);
            $adjacentInputIdValue = Arr::get($validator->getData(), $adjacentInputIdKey, null);

            if(!$adjacentInputIdKey || !$adjacentInputIdValue) { return false; }
           
            if(!$adjacentInputIdValue) { return false; }
            
            $customInput = \App\Models\CustomizationInput::where(['id' => $adjacentInputIdValue])->first();
            
            if(! $customInput) { return false; }

            dd($customInput);

            $formData = $form->form;

            $formFieldCollection = array_column($formData, 'name');
            
            if(! $formFieldCollection) { return false; }

            if(! \in_array($value, $formFieldCollection)) { return false; }

            return true;
        });

        Validator::replacer('is_static_text', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Invalid Form Field',$message);
        });

        Validator::extend('is_static_file', function ($attribute, $value, $parameters, $validator) {
            $type = $parameters[0];
            $input_id = $parameters[1];
            $separator = '.';
            $attributeKeys = explode($separator, $attribute);
            dd($attributeKeys);
            $adjacentInputIdKey = str_replace('*', $attributeKeys[1], $type);
            $adjacentInputIdValue = Arr::get($validator->getData(), $adjacentInputIdKey, null);

            if(!$adjacentInputIdKey || !$adjacentInputIdValue) { return false; }
           
            if(!$adjacentInputIdValue) { return false; }
            
            $customInput = \App\Models\CustomizationInput::where(['id' => $adjacentInputIdValue])->first();
            
            if(! $customInput) { return false; }

            dd($customInput);

            $formData = $form->form;

            $formFieldCollection = array_column($formData, 'name');
            
            if(! $formFieldCollection) { return false; }

            if(! \in_array($value, $formFieldCollection)) { return false; }

            return true;
        });

        Validator::replacer('is_static_file', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Invalid Form Field',$message);
        });

        Validator::extend('required_only', function ($attribute, $value, $parameters, $validator) {
            
            $module_id = Arr::get($validator->getData(), $parameters[0], null);

            if(!$module_id) { return false; }

            $module = \App\Models\Module::find($module_id);

            if(! $module) { return false; }

            $moduleType = $module->type;

            if(! $moduleType) { return false; }


            if($moduleType == 'image') { return false; }

            return true;
        });

        Validator::replacer('required_only', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Field is Required',$message);
        });

        Validator::extend('array_unique', function($attribute, $value, $parameters, $validator) {
            $table = $parameters[0];
            $column = $parameters[1];
            $ref = $parameters[2];
            $id = $parameters[3];

            $values = DB::table($table)->where($ref, $id)->pluck($column, 'id');

            $attributes = explode(".", $attribute);
            $data = $validator->getData();

            $items = $data[$attributes[0]];

            foreach($items as $key => $item)
            {
                $values[$key] = $item[$attributes[2]];
            }

            $counts = array_count_values($values);

            return $counts[$value] < 2;
        });

        Validator::replacer('array_unique', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'Already Exist',$message);
        });
        Validator::extend('is_appuser_campaign_exists',function($attribute, $value, $parameters, $validator){
            $username = $parameters[0];
            $campaign_code = $value;

            if(!$campaign_code || !$username) { return false; }


            $campaign = \App\Models\Campaign::where(['cid' => $campaign_code])->first();
            if(! $campaign) { return false; }
            $campaign_id = $campaign->id;


       //    \DB::enableQueryLog();
            /*
            
            $is_user_geography_exist = \App\Models\User::with(['geography' => function($q) use($campaign_id) {

                                            $q->where('campaign_id', '=', $campaign_id);
                                            $q->where('deleted_at', '=', NULL);
                                        }]
                                    )
                                        ->where(['username' => $username,'deleted_at'=>NULL])
                                        ->has('geography')
                                        ->count();*/

 $is_user_geography_exist = \App\Models\User::select('users.*')
        ->join('user_geographies',function($join){
            $join->on('user_geographies.user_id','=','users.id');
        })
        ->join('geographies',function($join) use ($campaign_id){
            $join->on('geographies.id','=','user_geographies.geography_id');
            $join->where('geographies.campaign_id', '=', $campaign_id);
            $join->whereNull('geographies.deleted_at');
        })
       ->where(['users.username' => $username,'users.deleted_at'=>NULL])
       ->count();

     //   $query = \DB::getQueryLog();
            

                                        
        if( (int)$is_user_geography_exist > 0) {
                return true;
        }
            return false;

        });


Validator::extend('is_course_of_campaign', function ($attribute, $value, $parameters, $validator)
        {
            list($input_campaign) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);
            $input_course_name = $value;       
            

            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name])->first(); 
            if(\is_null($campaign)) { return false; }
            
            $course = \App\Models\Course::where(['name' => $input_course_name, 'campaign_id' => $campaign->id])->first(); 
            if(\is_null($course)) { return false; }

            return true;
        });

        Validator::replacer('is_course_of_campaign', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'course does not belongs to campaign',$message);
        });


Validator::extend('is_section_of_course', function ($attribute, $value, $parameters, $validator)
        {
            list($input_course) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_course_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_course, null);
            $input_section_name = $value;       
            

            $course = \App\Models\Course::where(['name' => $input_course_name])->first(); 
            if(\is_null($course)) { return false; }
            
            $section = \App\Models\Section::where(['title' => $input_section_name, 'course_id' => $course->id])->first(); 
            if(\is_null($section)) { return false; }

            return true;
        });

        Validator::replacer('is_section_of_course', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'section does not belongs to course',$message);
        });
    Validator::replacer('is_appuser_campaign_exists', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'user does not exist for campaign',$message);
        });

/*
Validator::extend('check_question_type', function ($attribute, $value, $parameters, $validator)
        {

            $index = explode('.', $attribute, 3)[1] ;


            $input_question_id = Arr::get($validator->getData(),'question.'.$index.'.id', null);
            $input_ans = Arr::get($validator->getData(),'question.'.$index.'.answer', null);
            $input_other_ans = Arr::get($validator->getData(),'question.'.$index.'.other_answer', null);
            

            $que = \App\Models\QuizQuestions::where(['id' => $input_question_id])->first(); 


            if(in_array($que->question_type,['multiple_choices','radio','dropdown'])){
                
                if(!empty($input_ans) && is_array($input_ans)){

                    foreach($input_ans as $a){

                        if(! Uuid::isValid($a)){
                            return false;
                        }
                      }

                      return true ;
                }else{
                    return false;        
                }             
            
            }
        else if(in_array($que->question_type,['audio','video','text'])){

            
                if(empty($input_other_ans)){return false;}

                $extention = explode('.',$input_other_ans)[1];

                if($que->question_type == "audio"){
                        if(!in_array($extention,['wav','mp3'])){return false;}
                }

            if($que->question_type == "video"){
                    if(!in_array($extention,['avi','mp4','mov','flv','avg'])){return false;}
                }
                
                return true;
            }
            
        });

       Validator::replacer('check_question_type', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Answer or Other Answer format is invalid!',$message);
        });
*/

Validator::extend('check_other_answer_required', function ($attribute, $value, $parameters, $validator)
        {

            $index = explode('.', $attribute, 3)[1] ;


            $input_question_id = Arr::get($validator->getData(),'question.'.$index.'.id', null);
            
            $input_other_ans = Arr::get($validator->getData(),'question.'.$index.'.other_answer', null);
            

            $que = \App\Models\QuizQuestions::where(['id' => $input_question_id])->first(); 


            if(in_array($que->settings['input_answer_type'],['audio','video','text'])){

            
                if(empty($input_other_ans)){return false;}

               $extention = pathinfo($input_other_ans, PATHINFO_EXTENSION);


                if($que->settings['input_answer_type'] == "audio"){
                        if(!in_array($extention,['wav','mp3','3gp'])){return false;}
                }

            if($que->settings['input_answer_type'] == "video"){

                    if(!in_array($extention,['avi','mp4','mov','flv','avg'])){

                        return false;
                    }
                }
                
            }
            return true;
            
        });

       Validator::replacer('check_other_answer_required', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Other Answer is Required or Invalid!',$message);
        });


Validator::extend('check_answer_required', function ($attribute, $value, $parameters, $validator)
        {

            $index = explode('.', $attribute, 3)[1] ;


            $input_question_id = Arr::get($validator->getData(),'question.'.$index.'.id', null);
            $input_ans = Arr::get($validator->getData(),'question.'.$index.'.answer', null);
            

            $que = \App\Models\QuizQuestions::where(['id' => $input_question_id])->first(); 


            if(in_array($que->settings['input_answer_type'],['multiple_choices','radio','dropdown'])){
                
                if(!empty($input_ans) && is_array($input_ans)){

                    foreach($input_ans as $a){

                        if(! Uuid::isValid($a)){
                            return false;
                        }
                      }

                      return true ;
                }else{
                    return false;        
                }             
            }
            return true ;
        });

       Validator::replacer('check_answer_required', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Answer is Required or Invalid!',$message);
        });


Validator::extend('check_media_required', function ($attribute, $value, $parameters, $validator)
        {

            $media_file = Arr::get($validator->getData(),'media_file', null);
            if(in_array($value,['audio','image'])){
                
                if(empty($media_file)){return false;}             

                $extention = $media_file->extension();


                if($value == "audio"){
                        if(!in_array($extention,['wav','mp3'])){return false;}
                }

            if($value == "image"){

                    if(!in_array($extention,['jpg','jpeg','png'])){

                        return false;
                    }
                }

            }

            return true ;
        });

       Validator::replacer('check_media_required', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Media File Required / File Format Invalid.',$message);
        });


Validator::extend('check_video_required_field', function ($attribute, $value, $parameters, $validator)
        {

            $media_file = Arr::get($validator->getData(),'media_file', null);
            $video_type = Arr::get($validator->getData(),'video_type', null);
            
            

            if(in_array($value,['video'])){
                
                if(empty($media_file) && empty($video_type)){

                    return false;        
                }

                if(!empty($media_file)){
                        $extention = $media_file->extension();
                
                    if(!in_array($extention,['avi','mp4','mov','flv','avg'])){

                        return false;
                    }
                 }            
            }
            return true ;
        });

       Validator::replacer('check_video_required_field', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Media File or Video Type Required',$message);
        });



       Validator::extend('check_lesson_type_video', function ($attribute, $value, $parameters, $validator)
        {

            
            $type = Arr::get($validator->getData(),'type.name', null);
            $media_file = Arr::get($validator->getData(),'media_file', null);
            $video_type = Arr::get($validator->getData(),'video_type.name', null);
          
            if($type == "video"){
                if($media_file == null && $video_type == null){
                    return false;
                }
                
            }

            return true;            
        });


       Validator::replacer('check_lesson_type_video', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Media File or Video Link field required.',$message);
        });


       

    Validator::extend('check_lesson_type_pdf_image', function ($attribute, $value, $parameters, $validator)
        {

            
            $type = Arr::get($validator->getData(),'type.name', null);
            $media_file = Arr::get($validator->getData(),'media_file', null);
            

            if($type == "pdf" || $type == "image"){
                if($media_file == null){
                    return false;
                }
            }

            return true;            
        });


       Validator::replacer('check_lesson_type_pdf_image', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Media File required.',$message);
        });


    Validator::extend('checkUserReviewerRole', function ($attribute, $value, $parameters, $validator)
        {

            $course_id = $value ; 
            list($user_id) = $parameters;
            
            
            $res_role = \App\Models\ModelHasRoles::where(['course_id' => $value,'model_uuid'=>$parameters[0]])
                 ->join('roles',function($join){
                             $join->on('roles.id','=','model_has_roles.role_id');
                        })->first();



            if(empty($res_role)){
                return false;
            }


            if(!in_array($res_role->name,['reviewer','super-user'])){
                return false;
            }

                return true;            
        });


       Validator::replacer('checkUserReviewerRole', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'User Unauthorized To Access Course !!!',$message);
        });
       

    Validator::extend('is_belong_course', function ($attribute, $value, $parameters, $validator)
        {

            $quiz_id = $value ; 
            list($course_id) = $parameters;
            
            
            $res_quiz = \App\Models\Quiz::where(['quiz.id' => $quiz_id,'quiz.deleted_at'=>NULL])
                 ->join('sections',function($join){
                             $join->on('sections.id','=','quiz.section_id');
                        })
                    ->where('sections.course_id','=',$course_id)
                    ->first();
                    
            if(empty($res_quiz)){
                return false;
            }

            
                return true;            
        });


       Validator::replacer('is_belong_course', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Quiz not belong to the Course.',$message);
        });
  
    Validator::extend('is_belong_to_quiz', function ($attribute, $value, $parameters, $validator)
        {

            $answer_form_id = $value ; 
            list($quiz_id,$user_id) = $parameters;
            
            
            $res_answerform = \App\Models\QuizAnswersForm::where(['quiz_id' => $quiz_id,'user_id'=>$user_id,'id'=>$answer_form_id,'deleted_at'=>NULL])->first();
                    
            if(empty($res_answerform)){
                return false;
            }

            
                return true;            
        });


       Validator::replacer('is_belong_to_quiz', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Answer Form not belongs to the Quiz.',$message);
        });
       
Validator::extend('check_lesson_type_article', function ($attribute, $value, $parameters, $validator)
        {

            
            $type = Arr::get($validator->getData(),'type.name', null);
            $article_link = Arr::get($validator->getData(),'article_link', null);
            

            if($type == "article" ){
                if($article_link == null){
                    return false;
                }
            }

            return true;            
        });


       Validator::replacer('check_lesson_type_article', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Article Link required.',$message);
        });


       Validator::extend('check_lesson_type', function ($attribute, $value, $parameters, $validator)
        {

            
            $duration = Arr::get($validator->getData(),'duration', null);
            $lesson = \App\Models\Lesson::where(['id' => $value])->first(); 

            if(!empty($lesson)){
                if($lesson->type == "video" && ($duration == null/* || $duration == "00:00:00" */)){
                    return false;
                }
            }

            return true;            
        });

       Validator::replacer('check_lesson_type', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, 'Duration field required.',$message);
        });

Validator::extend('is_user_belong_to_group', function ($attribute, $value, $parameters, $validator)
        {

            $group_id = Arr::get($validator->getData(),'group_id', null);
            $user_id = $value;
            
            if(!$group_id){
                return false;
            }

           

                $res = \App\Models\GroupUser::where(['group_id' => $group_id,'user_id'=>$user_id])->first();

                $check_user =  \App\Models\User::where(['id' => $user_id,'deleted_at'=>NULL])->first();
                
                if(!$res && !$check_user){
                    return false ;
                }

            return true ;
        });

       Validator::replacer('is_user_belong_to_group', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, ' User not belong to group.',$message);
        });


Validator::extend('is_course_belong_group', function ($attribute, $value, $parameters, $validator)
        {
             list($input_company, $input_campaign,$input_group) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);

            $input_group_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_group, null);          
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }


            $group = \App\Models\Group::where(['name' => $input_group_name, 'campaign_id' => $campaign->id])->first(); 
            if(\is_null($group)) { return false; }

        
            $course = \App\Models\Course::where(['group_id' => $group->id,'campaign_id'=>$campaign->id,'name'=>$value])->first(); 
            if(\is_null($course)) { return false; }



            return true;
        
        });

       Validator::replacer('is_course_belong_group', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, ' Course not belong to group.',$message);
        });



Validator::extend('check_user_designation', function ($attribute, $value, $parameters, $validator)
        {
             list($input_company, $input_campaign,$input_group,$input_designation) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);

            $input_group_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_group, null);          

            $input_designation_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_designation, null); 
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }


            $group = \App\Models\Group::where(['name' => $input_group_name, 'campaign_id' => $campaign->id])->first(); 
            if(\is_null($group)) { return false; }

             $designation = \App\Models\Designation::where(['name' => $input_designation_name])->first(); 
            if(\is_null($designation)) { return false; }

        
            $user = \App\Models\User::where(['designation_id' => $designation->id,'campaign_id'=>$campaign->id,'username'=>$value])->first(); 
            if(\is_null($user)) { return false; }


            $usergroup = \App\Models\GroupUser::where(['group_id' => $group->id,'user_id'=>$user->id])->first(); 

            if(\is_null($usergroup)) { return false; }

                return true;

        });

       Validator::replacer('check_user_designation', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, ' User not belong to group or designation.',$message);
        });



Validator::extend('is_unique_course_name', function ($attribute, $value, $parameters, $validator)
        {
             list($input_company, $input_campaign) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);

            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }


        
            $checkcourse = \App\Models\Course::where(['campaign_id' => $campaign->id,'name'=>$value])->first(); 
            if(!empty($checkcourse)) { return false; }


                return true;

        });

       Validator::replacer('is_unique_course_name', function ($message, $attribute, $rule, $parameters) {

            return str_replace($message, ' Course Name Already Exists.',$message);
        });


        Validator::extend('is_user_campaign_exists', function ($attribute, $value, $parameters, $validator)
        {

            $username = $value;
            $campaign_code = Arr::get($validator->getData(), $parameters[0], null);

            if(!$campaign_code || !$username) { return false; }


            $campaign = \App\Models\Campaign::where(['cid' => $campaign_code])->first();
            if(! $campaign) { return false; }
            $campaign_id = $campaign->id;


            $is_user_geography_exist = \App\Models\User::select('*')
                    ->Join('user_geographies',function($join){
                             $join->on('user_geographies.user_id','=','users.id');
                        })
                    ->Join('geographies',function($join){
                             $join->on('geographies.id','=','user_geographies.geography_id');
                            $join->whereNull('geographies.deleted_at');
                        })->where(['geographies.campaign_id'=>$campaign_id,"users.username"=>$username])
                        ->count();


            
            if( (int)$is_user_geography_exist > 0) {
                return true;
            }



            return false;
        });

        Validator::replacer('is_user_campaign_exists', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'user does not exist for campaign',$message);
        });

        Validator::extend('is_type_of_input', function ($attribute, $value, $parameters, $validator) {
            $type = $parameters[0];
            $separator = '.';
            $attributeKeys = explode($separator, $attribute);

            $adjacentInputIdKey = str_replace('*', $attributeKeys[1], $type);
            $adjacentInputIdValue = Arr::get($validator->getData(), $adjacentInputIdKey, null);

            if(!$adjacentInputIdKey || !$adjacentInputIdValue) { return false; }
            
            $inputData = \App\Models\CustomizationInput::where(['id' => $adjacentInputIdValue, 'type' => $value])->first();
            if(! $inputData) { return false; }

            return true;
        }, 'The :attribute is not valid for input');

        Validator::extend('is_file_by_module', function ($attribute, $value, $parameters, $validator) {
            $moduleId = $parameters[0];
            
            $module_id = Arr::get($validator->getData(), $moduleId, null);

            if(! $module_id) { return false; }

            $module = \App\Models\Module::where(['id' => $module_id])->first();

            if(! $module) {
                return false;
            }

            $moduleType = $module->type;

            if(in_array($moduleType, ['video']) && !in_array($value->getMimeType(), ['video/mp4']) ) {
                return false;
            } else if (in_array($moduleType, ['gif']) && !in_array($value->getMimeType(), ['image/gif']) ) {
                return false;
            }

            return true;
        }, 'The :attribute is not valid for module');

        Validator::extend('is_group_of_campaign', function ($attribute, $value, $parameters, $validator)
        {
            list($input_company, $input_campaign) = $parameters;
            list($attribute_row, $attribute_name) = \explode('.', $attribute, 2);

            $input_company_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_company, null);
            $input_campaign_name = Arr::get($validator->getData(), $attribute_row.'.'.$input_campaign, null);          
            
            $company = \App\Models\Company::where(['name' => $input_company_name])->first(); 
            if(\is_null($company)) { return false; }
            
            $campaign = \App\Models\Campaign::where(['name' => $input_campaign_name, 'company_id' => $company->id])->first(); 
            if(\is_null($campaign)) { return false; }
                        
            $campaign_id = $campaign->id;

            $group = \App\Models\Group::where(['campaign_id' => $campaign_id, 'name' => $value])->first(); 
            if(\is_null($campaign)) { return false; }

            return true;
        });

        Validator::replacer('is_group_of_campaign', function ($message, $attribute, $rule, $parameters) {
            return str_replace($message, 'invalid group of campaign',$message);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
public function duplicate_geography_check($campaign_id, $name, $type, $parent_id = null)
    {
        $where = [];

        $where1 = ['campaign_id','=',$campaign_id];
        $where2 = ['type','=',$type];
        $where3 = ['name','like',$name];

        $where4 = [];
       /* if(! empty($parent_id)) {
            $where4 = ['parent_id','=',$parent_id];
        }*/

        $where = [$where1, $where2, $where3, $where4];
        $where = array_filter(array_map('array_filter', $where));
        
        return \App\Models\Geography::where($where)->exists();        
    }
}
