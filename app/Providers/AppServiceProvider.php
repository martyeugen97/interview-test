<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\BitcoinApiHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BitcoinApiHelper::class, function() {
            return new BitcoinApiHelper();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
