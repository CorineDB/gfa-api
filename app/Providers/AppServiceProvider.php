<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use SaiAshirwadInformatia\SecureIds\Facades\SecureIds;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        SecureIds::load();
        Schema::defaultStringLength(191);
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
