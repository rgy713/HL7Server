<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('push')->group(function() {
    Route::get('lis-send', 'Api\LisController@send')->name('api.lis.send');
    Route::post('lis-send', 'Api\LisController@send')->name('api.lis.send');
    Route::get('his-send', 'Api\HisController@send')->name('api.his.send');
    Route::post('his-send', 'Api\HisController@send')->name('api.his.send');
});

Route::prefix('push-lianyungang')->group(function() {
    Route::get('lis-send', 'Api\LisControllerLianyungang@send')->name('api.lis.send');
    Route::post('lis-send', 'Api\LisControllerLianyungang@send')->name('api.lis.send');
    Route::get('his-send', 'Api\HisControllerLianyungang@send')->name('api.his.send');
    Route::post('his-send', 'Api\HisControllerLianyungang@send')->name('api.his.send');
});