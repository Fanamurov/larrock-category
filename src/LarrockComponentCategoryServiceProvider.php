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
        $timestamp_after = date('Y_m_d_His', time()+10);
        $migrations = [];
        if ( !class_exists('CreateCategoryTable')){
            $migrations[__DIR__.'/database/migrations/0000_00_00_000000_create_category_table.php'] = database_path('migrations/'.$timestamp.'_create_category_table.php');
        }
        if ( !class_exists('CreateCategoryLinkTable')){
            $migrations[__DIR__.'/database/migrations/0000_00_00_000000_create_category_link_table.php'] = database_path('migrations/'.$timestamp.'_create_category_link_table.php');
        }
        if ( !class_exists('AddForeignKeysToCategoryTable')){
            $migrations[__DIR__.'/database/migrations/0000_00_00_000000_add_foreign_keys_to_category_table.php'] = database_path('migrations/'.$timestamp_after.'_add_foreign_keys_to_category_table.php');
        }
        if ( !class_exists('AddForeignKeysToCategoryLinkTable')){
            $migrations[__DIR__.'/database/migrations/0000_00_00_000000_add_foreign_keys_to_category_link_table.php'] = database_path('migrations/'.$timestamp_after.'_add_foreign_keys_to_category_link_table.php');
        }

        $this->publishes($migrations, 'migrations');

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/larrock')
        ], 'views');
    }
}