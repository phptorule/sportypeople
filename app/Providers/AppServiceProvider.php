<?php

namespace App\Providers;

use Artisan;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		Artisan::call('migrate', array('--path' => 'database/migrations', '--force' => true));
        //
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
