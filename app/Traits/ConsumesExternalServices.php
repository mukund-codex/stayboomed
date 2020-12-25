<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;
use Log;

trait ConsumesExternalServices
{
    public function performRequest(string $method, string $requestUrl, Request $request = null)
    {   
        try {
            extract(\parse_url($requestUrl));
        } catch (Exception $e) {
            return \response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Invalid Request Url'
            ], 500);
        }

        $options = [];
        $options['debug'] = FALSE;

        $requestHeaders = $request->header();
        $headers = [];
        array_walk($requestHeaders, function($val, $key) use (&$headers) {
            $headers[$key]= array_pop($val);
        });
        
        $client = new Client([
            'base_uri' => "$scheme://$host",
            'headers' => $headers
        ]);
            
        if(
            $request->header('Content-Type') != null 
            && strpos($request->header('Content-Type'), 'multipart/form-data') !== FALSE
            ) {
                $multipart_output = [];
                foreach($request->input() as $key => $val) {
                    $temp = [];
                    $temp['name'] = $key;
                    $temp['contents'] = $val;
                    array_push($multipart_output, $temp);
                }

                foreach($request->file() as $k1 => $v1){
                    $temp = [];
                    if(is_array($request->file($k1))) {
                        foreach ($request->file($k1) as $k2 => $v2) {
                            $temp['name'] = $k1.'[]';
                            $temp['contents'] = fopen($v2->getPathname(), 'r');
                            $temp['filename'] = $v2->getClientOriginalName();           
                        }    
                    } else {
                        $temp['name'] = $k1;
                        $temp['contents'] = fopen($v1->getPathname(), 'r');
                        $temp['filename'] = $v1->getClientOriginalName();
                    }
                    array_push($multipart_output, $temp);
                }

            $options['multipart'] = $multipart_output;
        } 
        if(
            $request->header('Content-Type') != null 
            && strpos($request->header('Content-Type'), 'application/x-www-form-urlencoded') !== FALSE
            ) {
            $options['form_params'] = $request->all();
        } 
        if(
            $request->header('Content-Type') != null 
            && strpos($request->header('Content-Type'), 'application/json') !== FALSE
            ) {
            $options['json'] = $request->all();
        } 
    
        try {
            $response = $client->request($method, $path, $options);
            
            Log::info('External Service Call', [
                'method' => $method,
                'path' => $path,
                'options' => $options
            ]);
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();

                $status = $response->getStatusCode();
                $reason = $response->getReasonPhrase();
                $body = (string) $response->getBody();
                $responseBodyAsString = $response->getBody()->getContents();

                Log::info('External Service Call Failed', [
                    'status' => $status,
                    'reason' => $reason,
                    'body' => $body,
                    'responseBodyString' => $responseBodyAsString
                ]);
            }
        }

        $responseFormat = [];

        $responseBody = json_decode($response->getBody(), true);
        
        $responseFormat['success'] = isset($responseBody['success']) ? $responseBody['success'] : in_array($response->getStatusCode(), [200,201]);
        $responseFormat['status'] = isset($responseBody['status']) && !is_bool($responseBody['status']) ? $responseBody['status'] : $response->getStatusCode();
        $responseFormat['message'] = isset($responseBody['message']) ? $responseBody['message'] : $response->getReasonPhrase(); 
        $responseFormat['error'] = isset($responseBody['error']) ? $responseBody['error'] : []; 
        $responseFormat['data'] = isset($responseBody['data']) ? $responseBody['data'] : []; 
        
        return $responseFormat;
    }
}