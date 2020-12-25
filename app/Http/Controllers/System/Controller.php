<?php

namespace App\Http\Controllers\System;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use App\Http\Controllers\JsonDataSerializer;
use App\Traits\ResponseTrait;
use App\Traits\StoreImageTrait;

abstract class Controller extends BaseController
{
    use ResponseTrait, StoreImageTrait;

    private $filtered;

    /**
    * Constructor
    *
    * @param Manager|null $fractal
    */
    public function __construct(Manager $fractal = null)
    {
        $fractal = $fractal === null ? new Manager() : $fractal;
        // $fractal->setSerializer(new JsonDataSerializer());
        $this->setFractal($fractal);
    }


    /**
     * Validate HTTP request against the rules
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @return bool|array
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [])
    {
        // Perform Validation
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->messages();
            
            // $errorMessages = array_merge($errorMessages, $messages);
            
            // create error message by using key and value
            foreach ($errorMessages as $key => $value) {
                $errorMessages[$key] = $value[0];
            }
            return $errorMessages;
        }
        return true;
    }

    /**
     * Accept HTTP request & filter query params
     *
     * @param Request $request
     * @param array $include
     * @return bool|array
     */
    protected function filteredRequestParams(Request $request, array $include): array
    {
        $this->filtered = []; 
        if(\is_array($request->all())) {
            foreach ($request->all() as $key => $value) {
                if(\in_array($key, $include) && $request->filled($key)) {
                    $this->filtered[$key] = trim($value);
                }
            }
            return $this->filtered;
        }

        return [];
    }

    /**
     * Replace Array Keys with Set Keys
     *
     * @param array $arr
     * @param array $set
     * @return array
     */
    protected function recursive_change_key($arr, $set) {
        if (is_array($arr) && is_array($set)) {
            $newArr = array();
            foreach ($arr as $k => $v) {
                $key = array_key_exists($k, $set) ? $set[$k] : $k;
                $newArr[$key] = is_array($v) ? recursive_change_key($v, $set) : $v;
            }
            return $newArr;
        }
        return $arr;    
    }

    /**
     * Accept HTTP request & filter query params
     *
     * @param Request $request
     * @param array $include
     * @param array $operator
     * @param array $recursive_keys
     * @param array $table_group
     * @return bool|array
     */
    protected function filter_data(Request $request, array $include, array $operator, array $recursive_keys, array $table_group = [])
    {
        $complex = [];
        $filter_params = $this->filteredRequestParams($request, $include);
        $recursive_change_keys = $this->recursive_change_key($filter_params, $recursive_keys);

        if(count($table_group) > 0) {
            foreach ($table_group as $key => $value) {
                
                $_filter_keys = \array_intersect_key($filter_params, $recursive_keys, $operator, \array_flip($value));
                $_recursive_keys = \array_intersect_key($recursive_keys, $filter_params, $operator, \array_flip($value));
                $_operator = \array_intersect_key($operator, $filter_params, $recursive_keys, \array_flip($value));
                
                $complex[$key] = \array_filter(array_map(function ($column, $operator, $value) {
                    if($column && $operator && $value) {
                        return array($column, $operator, (\strtoupper($operator) == "ILIKE") ? "%$value%" : $value);
                    }
                }, $_recursive_keys, $_operator, $_filter_keys));
            }            
        } else {
            foreach ($filter_params as $key => $value) {
                $single = [];
                $single = array($recursive_keys[$key], $operator[$key], (\strtoupper($operator[$key]) == "ILIKE") ? "%$value%" : $value);
                array_push($complex, $single);
            }
        }

        return $complex;
    }

}
