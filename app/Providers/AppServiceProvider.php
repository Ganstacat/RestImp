<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /* 
         mysql quantity of bytes for indexes is about 764,
         laravel uses 4byte characters, which means max length
         of index string should not exceed 191
         without this, migration may fail (Laravel 5.4)
        */
        // Schema::defaultStringLength(191);
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
