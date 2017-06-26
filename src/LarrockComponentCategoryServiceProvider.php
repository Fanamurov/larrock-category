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
        $this->loadViewsFrom(__DIR__.'/views', 'larrock');

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/larrock')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        $this->app->make(CategoryComponent::class);

        $timestamp = date('Y_m_d_His', time());
        $migrations = [];
        if ( !class_exists('CreateLarrockCategoryTable')){
            $migrations = [__DIR__.'/../database/migrations/0000_00_00_000000_create_category_table.php' => database_path('migrations/'.$timestamp.'_create_category_table.php')];
        }
        if ( !class_exists('CreateLarrockCategoryLinkTable')){
            $migrations = [__DIR__.'/../database/migrations/0000_00_00_000000_create_category_link_table.php' => database_path('migrations/'.$timestamp.'_create_category_link_table.php')];
        }
        if ( !class_exists('AddForeignKeysToCategoryTable')){
            $migrations = [__DIR__.'/../database/migrations/0000_00_00_000000_add_foreign_keys_to_category_table.php' => database_path('migrations/'.$timestamp.'_add_foreign_keys_to_category_table.php')];
        }
        if ( !class_exists('AddForeignKeysToLarrockCategoryLinkTable')){
            $migrations = [__DIR__.'/../database/migrations/0000_00_00_000000_add_foreign_keys_to_category_link_table.php' => database_path('migrations/'.$timestamp.'_add_foreign_keys_to_category_link_table.php')];
        }

        $this->publishes([
            $migrations
        ], 'migrations');
    }
}
