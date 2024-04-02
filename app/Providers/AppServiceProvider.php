<?php

namespace App\Providers;

use App\Models\Setting;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      $rs=Setting::all(['name','value'])->keyBy('name')->transform(function ($setting) { return $setting->value; })->toArray();
        config([
            'global' => $rs
        ]);
    }
}
