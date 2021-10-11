<?php
use Illuminate\Support\Facades\Route;

Route::get('/','App\Http\Controllers\IndexController@index')->name('test.index');
//通过前台get请求'/'来执行IndexController控制器的index方法。命名为'test.index'。
Route::get('get','App\Http\Controllers\IndexController@get')->name('test.get');
//通过前台get请求'get'来执行IndexController控制器的get方法。命名为'test.get'。
Route::get('/test1','App\Http\Controllers\TestController@Test')->name('test.get');
