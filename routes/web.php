<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::prefix('setting')->group(function() {
    Route::get('index', 'SettingController@index')->name('setting.index');
    Route::post('update', 'SettingController@update')->name('setting.update');
});

Route::any('his-lis-service', 'SoapCallbackController@service');