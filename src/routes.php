<?php

Route::group(['prefix' => 'admin', 'middleware'=> ['web', 'level:2', 'LarrockAdminMenu', 'SaveAdminPluginsData', 'SiteSearchAdmin']], function(){
    Route::resource('category', 'Larrock\ComponentCategory\AdminCategoryController');
    Route::post('/category/storeEasy', 'Larrock\ComponentCategory\AdminCategoryController@storeEasy');
});