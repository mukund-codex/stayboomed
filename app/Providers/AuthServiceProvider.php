<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Dusterio\LumenPassport\LumenPassport;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        
        Route::group(['middleware' => 'passport_administrators'], function () {
            LumenPassport::routes($this->app);
        });
        LumenPassport::allowMultipleTokens();
        LumenPassport::tokensExpireIn(Carbon::now()->addSeconds( env('API_ACCESS_TOKEN_TTL', 1200) ));

        // $this->app['auth']->viaRequest('api', function ($request) {
        //     if ($request->input('api_token')) {
        //         return User::where('api_token', $request->input('api_token'))->first();
        //     }
        // });
    }
}
