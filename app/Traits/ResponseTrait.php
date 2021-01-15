<?php 

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;

trait ResponseTrait 
{
    /**
     * Status code of response
     *
     * @var int
     */
     protected $statusCode = 200;

    /** 
     * Fractal Manager Instance 
     */
    protected $fractal;

    /**
    * Getter for statusCode
    *
    * @return mixed
    */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    /**
    * Setter for statusCode
    *
    * @param int $statusCode Value to set
    *
    * @return self
    */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /** Set Fractal Manager Instances
     *  @param Manager $fractal
     *  @return void
     */
    public function setFractal(Manager $fractal)
    {
        $this->fractal = $fractal;
    }

    /**
    * Proxy a request to the OAuth server.
    *
    * @param string $grantType what type of grant type should be proxied
    * @param array $data the data to send to the server
    */
    
    public function proxy($grantType, array $data = [])
    {
        if(empty(env('PASSPORT_CLIENT_ID')) || empty(env('PASSPORT_CLIENT_SECRET'))) {
            throw new Exception("Passport Client Credentials Required");
        }

        $data = array_merge($data, [
            'client_id'     => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'grant_type'    => $grantType,
            'scope'         => ''
        ]);
          
        $client = new \GuzzleHttp\Client();      
        
        try {
            $result = $client->request('POST', url('oauth/token'), [
                'form_params' => $data,
                'http_errors' => false
            ]);   
        
        } catch (ClientException | RequestException | Exception $e) {
            throw new Exception("{$e->getMessage()}");
        }

        $data = json_decode((string) $result->getBody(), true);
        return $data;
    }

    /**
     * Return Collection response from the Application
     *  
     * @param array|LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection $collection
     * @param \Closure|TransformerAbstract $callback;
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithCollection($collection, $callback, bool $success, int $status, string $message = '', $extension = [])
    {   
        $this->fractal = new Manager();
        $resources = new Collection($collection, $callback);

        if(empty($collection)) {
            $collection = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $resources = new Collection($collection, $callback);
        }
        
        $resources->setPaginator(new IlluminatePaginatorAdapter($collection));
        
        $rootScope = $this->fractal->createData($resources);
        
        return $this->responseJson($success, $status, $message, [], $rootScope->toArray(), $extension);
    }
    /**
     * Return Collection response from the Application
     *  
     * @param array|\Illuminate\Database\Eloquent\Collection $collection
     * @param \Closure|TransformerAbstract $callback;
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithCollectionAll($collection, $callback, bool $success, int $status, string $message = '', $extension = [])
    {
        $resources = new Collection($collection, $callback);

        /* if(empty($collection)) {
            $collection = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $resources = new Collection($collection, $callback);
        } */
        
        // $resources->setPaginator(new IlluminatePaginatorAdapter($collection));
        
        $rootScope = $this->fractal->createData($resources);
        
        return $this->responseJson($success, $status, $message, [], $rootScope->toArray(), $extension);
    }

    /**
     * Return Single Item response from the Application
     * 
     * @param Model $item
     * @param \Closure|TransformerAbstract $callback
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithItem($item, $callback, bool $success, int $status, string $message = '', $extension = [])
    {
        $this->fractal = new Manager();
        $resources = new Item($item, $callback);
        $rootScope = $this->fractal->createData($resources);
      //  $data = array_merge($rootScope->toArray(),['progress'=>80]);
    

        return $this->responseJson($success, $status, $message, [],$rootScope->toArray(), $extension);
    }

    public function respondLessonWithItem($progress=0,$item, $callback, bool $success, int $status, string $message = '', $extension = [])
    {
        $resources = new Item($item, $callback);
        $rootScope = $this->fractal->createData($resources);
        $data = array_merge($rootScope->toArray(),['progress'=>(int)$progress]);
    

        return $this->responseJson($success, $status, $message, [],$data, $extension);
    }

    /** 
     * HTTP response in JSON Format
     * @param bool $success - Success Status [true|false]
     * @param int $status - HTTP Status Code [eg. 200]
     * @param string $message - Response Message
     * @param array $error - Response Error 
     * @param array|object $data - Response Data
     * @return \Illuminate\Http\JsonResponse $response
     */
    public function responseJson(bool $success, int $status, string $message = '', array $error = [], $data = [], $extension = []) {
        $result_data = !empty($data) ? ($this->findKey($data, 'data') ? $data : ['data' => $data]) : [];
        
        $error = empty($error) ? (object) $error : $error;

        return \response()->json(array_merge([
            'success'   =>  $success,
            'status'    =>  $status,
            'message'   =>  $message,
            'error'     =>  $error,
        ],$result_data, $extension), $status);
    }

    function findKey($array, $keySearch)
    {
        foreach ($array as $key => $item) {
            
            if ($key == $keySearch) {
                return true;
            } elseif (is_array($item) && findKey($item, $keySearch)) {
                return true;
            } else {
                return false;
            }
        }
    }
}   

?>