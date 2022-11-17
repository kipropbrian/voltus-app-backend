<?php

namespace App\Providers;

use App\Http\Controllers\FacePlusController;
use Illuminate\Support\ServiceProvider;

class FacePlusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton( FacePlusController::class,
            function ($app) {
                return new FacePlusController();
            }
        );
    }
}
