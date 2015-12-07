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

    Route::post('articles', '\PhpSoft\Articles\Controllers\ArticleController@store');
    Route::patch('articles/{id}', '\PhpSoft\Articles\Controllers\ArticleController@update');
    Route::post('articles/{id}/trash', '\PhpSoft\Articles\Controllers\ArticleController@moveToTrash');
    Route::post('articles/{id}/restore', '\PhpSoft\Articles\Controllers\ArticleController@restoreFromTrash');
    Route::post('articles/{id}/enable', '\PhpSoft\Articles\Controllers\ArticleController@enable');
    Route::post('articles/{id}/disable', '\PhpSoft\Articles\Controllers\ArticleController@disable');
    Route::delete('articles/{id}', '\PhpSoft\Articles\Controllers\ArticleController@destroy');
});

Route::get('categories/trash', '\PhpSoft\Articles\Controllers\CategoryController@index');
Route::get('categories/{idOrAlias}', '\PhpSoft\Articles\Controllers\CategoryController@show');
Route::get('categories', '\PhpSoft\Articles\Controllers\CategoryController@index');

Route::get('articles/{id}', '\PhpSoft\Articles\Controllers\ArticleController@show');
