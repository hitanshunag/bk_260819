<?php

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

Route::get('/', function () {
    return "OrderAPP Rest API";
});

Route::get('/api-docs', function () {
    return view('welcome');
});

Route::fallback(function(){
    return response()->json(['status' => 'Not Found.'], 404);
})->name('api.fallback.404');