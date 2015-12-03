<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware'=>'auth'], function() { // use middleware jwt.auth if use JSON Web Token

    Route::post('categories', '\PhpSoft\Articles\Controllers\CategoryController@store');
    Route::patch('categories/{id}', '\PhpSoft\Articles\Controllers\CategoryController@update');
    Route::post('categories/{id}/enable', '\PhpSoft\Articles\Controllers\CategoryController@enable');
    Route::post('categories/{id}/disable', '\PhpSoft\Articles\Controllers\CategoryController@disable');
    Route::post('categories/{id}/trash', '\PhpSoft\Articles\Controllers\CategoryController@moveToTrash');
    Route::post('categories/{id}/restore', '\PhpSoft\Articles\Controllers\CategoryController@restoreFromTrash');
    Route::delete('categories/{id}', '\PhpSoft\Articles\Controllers\CategoryController@destroy');
});

Route::get('categories/{idOrAlias}', '\PhpSoft\Articles\Controllers\CategoryController@show');
