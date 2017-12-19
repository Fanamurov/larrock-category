<?php

Route::group(['prefix' => 'admin'], function(){
    Route::resource('category', 'Larrock\ComponentCategory\AdminCategoryController');
    Route::post('/category/storeEasy', 'Larrock\ComponentCategory\AdminCategoryController@storeEasy');
});

Breadcrumbs::register('admin.'. LarrockCategory::getName() .'.index', function($breadcrumbs){
    $breadcrumbs->push(LarrockCategory::getTitle() .' /');
});