<?php

use Larrock\ComponentCategory\AdminCategoryController;

Route::group(['prefix' => 'admin', 'middleware'=> ['web', 'level:2', 'LarrockAdminMenu', 'SaveAdminPluginsData']], function(){
    Route::resource('category', AdminCategoryController::class);
    Route::post('/category/storeEasy', AdminCategoryController::class .'@storeEasy');
});