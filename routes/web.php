<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(
    [
        'prefix' => '',
        'middleware' => []
    ], 
    function() use ($router) {
   
        $router->group(['middleware' => []], function () use ($router) {
            $router->get('/', function () use ($router) {
                $res['success'] = true;
                $res['data'] = [
                    'app_name' => env('APP_NAME', true),
                    'app_version' => env('APP_VERSION',true),
                ];
                return response($res);
            });
        });  
        $router->group([
            'prefix' => '/api/v1/'
        ], function() use ($router) {

            $router->group(
                ['prefix' => 'user', 'namespace' => 'Master'], 
                function() use ($router) {
                    $router->get('', ['uses' => 'UserController@index']);
                    $router->post('/create', 'UserController@store');
                    $router->post('/create_artist/', 'UserController@storeArtist');
                    $router->post('/login', ['uses' => 'UserController@login']);
                    $router->post('/logout', ['middleware' => ['auth:users'], 'uses' => 'UserController@logout']);
                    $router->post('/refresh', 'UserController@refresh');
                    $router->get('/roles', ['middleware' => ['auth:users'], 'uses' => 'UserController@roles']);
                    $router->put('/update/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}', ['uses' => 'UserController@update']);
                    $router->put('/update_artist/{id:[0-9]}', 'UserController@updateArtist');
                    $router->post('/import', ['middleware' => ['auth:users'], 'uses' => 'UserController@upload']);
                    $router->post('/profile', ['middleware' => ['auth:users'], 'uses' => 'UserController@profile']);
                    $router->post('/editprofile', ['middleware' => ['auth:users'], 'uses' => 'UserController@editprofile']);
                    $router->post('/resetpassword', ['middleware' => ['auth:users'], 'uses' => 'UserController@resetpassword']);
                    $router->get('/download', ['middleware' => ['auth:users'], 'uses' => 'UserController@download']);
                    /*, 'permission:export-user'*/
                    $router->delete('/delete/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}', ['middleware' => ['auth:users'], 'uses' => 'UserController@destroy']);
                    $router->post('/delete_all', ['middleware' => ['auth:users'], 'uses' => 'UserController@destroy_all']);
                    $router->post('/feedback', ['middleware' => ['auth:users'], 'uses' => 'UserController@feedback']); //, 'permission:user-feedback'
                    $router->get('/feedback_list', ['middleware' => ['auth:users', 'permission:feedback-list'], 'uses' => 'UserController@feedback_list']);
            });

            $router->group(['prefix' => 'state', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'StateController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'StateController@show']);
                $router->post('/create', 'StateController@store');
                $router->put('/update/{id:[0-9]}', 'StateController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'StateController@destroy']);
                
            });

            $router->group(['prefix' => 'city', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'CityController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'CityController@show']);
                $router->post('/create', 'CityController@store');
                $router->put('/update/{id:[0-9]}', 'CityController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'CityController@destroy']);
                
            });

            $router->group(['prefix' => 'profession', 'namespace' => 'Master'],  //, 'middleware' => ['auth:user']
            function() use ($router) {
                $router->get('', ['uses' => 'ProfessionController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'ProfessionController@show']);
                $router->post('/create', 'ProfessionController@store');
                $router->put('/update/{id:[0-9]}', 'ProfessionController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'ProfessionController@destroy']);
                
            });

            // $router->group(['prefix' => 'user', 'namespace' => 'Master'], 
            // function() use ($router) {
            //     $router->get('', ['uses' => 'UserController@index']);
            //     $router->get('/show/{id:[0-9]}', ['uses' => 'UserController@show']);
            //     $router->post('/create', 'UserController@store');
            //     $router->post('/create_artist/', 'UserController@storeArtist');
            //     $router->put('/update/{id:[0-9]}', 'UserController@update');
            //     $router->put('/update_artist/{id:[0-9]}', 'UserController@updateArtist');
            //     $router->delete('/delete/{id:[0-9]}', ['uses' => 'UserController@destroy']);
                
            // });

            $router->group(['prefix' => 'provider_details', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'ProviderDetailsController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'ProviderDetailsController@show']);
                $router->post('/create', 'ProviderDetailsController@store');
                $router->put('/update/{id:[0-9]}', 'ProviderDetailsController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'ProviderDetailsController@destroy']);
                
            });

            $router->group(['prefix' => 'job', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'JobController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'JobController@show']);
                $router->post('/create', 'JobController@store');
                $router->put('/update/{id:[0-9]}', 'JobController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'JobController@destroy']);
                
            });

            $router->group(['prefix' => 'subscription', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'SubscriptionController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'SubscriptionController@show']);
                $router->post('/create', 'SubscriptionController@store');
                $router->put('/update/{id:[0-9]}', 'SubscriptionController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'SubscriptionController@destroy']);
                
            });

            $router->group(['prefix' => 'artist_details', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'ArtistDetailsController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'ArtistDetailsController@show']);
                $router->post('/create', 'ArtistDetailsController@store');
                $router->put('/update/{id:[0-9]}', 'ArtistDetailsController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'ArtistDetailsController@destroy']);
                
            });

            $router->group(['prefix' => 'applied_jobs', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'AppliedJobsController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'AppliedJobsController@show']);
                $router->post('/create', 'AppliedJobsController@store');
                $router->put('/update/{id:[0-9]}', 'AppliedJobsController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'AppliedJobsController@destroy']);
                
            });

            $router->group(['prefix' => 'artist_subscription', 'namespace' => 'Master'],  //, 'middleware' => ['auth:user']
            function() use ($router) {
                $router->get('', ['uses' => 'ArtistSubscriptionController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'ArtistSubscriptionController@show']);
                $router->post('/create', 'ArtistSubscriptionController@store');
                $router->put('/update/{id:[0-9]}', 'ArtistSubscriptionController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'ArtistSubscriptionController@destroy']);
                
            });

            $router->group(['prefix' => 'paid_users', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'PaidUsersController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'PaidUsersController@show']);
                $router->post('/create', 'PaidUsersController@store');
                $router->put('/update/{id:[0-9]}', 'PaidUsersController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'PaidUsersController@destroy']);
                
            });

            $router->group(['prefix' => 'feedback', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'FeedbackController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'FeedbackController@show']);
                $router->post('/create', 'FeedbackController@store');
                $router->put('/update/{id:[0-9]}', 'FeedbackController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'FeedbackController@destroy']);
            });

            $router->group(['prefix' => 'refer', 'namespace' => 'Master'], 
            function() use ($router) {
                $router->get('', ['uses' => 'ReferController@index']);
                $router->get('/show/{id:[0-9]}', ['uses' => 'ReferController@show']);
                $router->post('/create', 'ReferController@store');
                $router->put('/update/{id:[0-9]}', 'ReferController@update');
                $router->delete('/delete/{id:[0-9]}', ['uses' => 'ReferController@destroy']);
            });
        });
    }
);
