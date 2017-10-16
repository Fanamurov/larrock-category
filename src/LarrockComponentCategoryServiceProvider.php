<?php

namespace Larrock\ComponentCategory;

use Illuminate\Support\ServiceProvider;

class LarrockComponentCategoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->loadViewsFrom(__DIR__.'/views', 'larrock');

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('larrockcategory', function() {
            $class = config('larrock.components.category', CategoryComponent::class);
            return new $class;
        });

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/larrock')
        ], 'views');
    }
}