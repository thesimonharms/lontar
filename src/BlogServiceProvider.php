<?php

namespace Lontar\Blog;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('api')->prefix('api')->group(__DIR__ . '/../routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/lontar.php' => config_path('lontar.php'),
        ], 'lontar-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lontar.php', 'lontar');
    }
}
