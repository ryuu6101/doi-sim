<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $models = ['User', 'EsimReport'];

        foreach ($models as $key => $value) {
            $plural = Str::plural($value);

            $this->app->bind(
                "App\\Repositories\\{$plural}\\{$value}RepositoryInterface",
                "App\\Repositories\\{$plural}\\{$value}Repository",
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
