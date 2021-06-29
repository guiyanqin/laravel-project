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

//调用了 auth:api 中间件用于验证用户的授权,系统默认声明的路由
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function (){
   echo phpinfo();
});

Route::resource('/events', 'API\EventsController');

Route::any('/book/{id}','App\Http\Controllers\BookController@getBookById');


Route::group(['prefix' => 'guangfeng','namespace'=> 'App\Modules\DigitalWin\GuangFengXiaXi\Controllers'], function (){
    //库存管理
    Route::group(['prefix' => 'stock'], function (){
        Route::get('/total', 'StockController@getTotal')->name('库存总量');
        Route::get('/statistics/type', 'StockController@typeStatistics')->name('烟叶类型统计');
        Route::get('/statistics/shape', 'StockController@shapeStatistics')->name('等级结构统计');
        Route::get('/statistics/level', 'StockController@levelStatistics')->name('等级结构统计');
        Route::get('/statistics/year', 'StockController@yearStatistics')->name('烟叶年份统计');
        Route::get('/options', 'StockController@getOptions')->name('获取选项');
        Route::get('/options/area', 'StockController@getArea')->name('获取区域选项');
        Route::get('/line_info', 'StockController@lineInfo')->name('库位信息获取');
        Route::get('/search', 'StockController@search')->name('物料检索');
        Route::get('/moisture', 'QualityController@lists')->name('水分检测');//无表guangfeng.item_moisture
        Route::get('/turnover_rate', 'StockController@getTurnoverRate')->name('周转率');
        Route::get('/order_log', 'StockController@getOrderLog')->name('出入库记录');
        Route::get('/flat_capacity', 'StockController@getFlatCapacity')->name('平库库容');
        Route::get('/high_capacity', 'StockController@getHighCapacity')->name('高架库库容');
    });
    //温湿度监控
    Route::group(['prefix' => 'humid'], function (){
        Route::get('/now', 'TemperatureHumidController@now')->name('当前温湿度');
        Route::get('/history', 'TemperatureHumidController@history')->name('历史温湿度');
        Route::get('/cladding', 'TemperatureHumidController@cladding')->name('包芯温度');
        Route::get('/cladding1', 'TemperatureHumidController@claddingDate')->name('包芯温度1');
    });
    //出入库
    Route::group(['prefix' => 'task'], function (){
        Route::get('/in_options', 'TaskController@getInOptions')->name('获取入库检索选项');
        Route::get('/in_search', 'TaskController@inSearch')->name('入库检索');
        Route::get('/in_detail', 'TaskController@inDetail')->name('收货录入明细');
        Route::get('/out_options', 'TaskController@getOutOptions')->name('获取出库检索选项');
        Route::get('/out_search', 'TaskController@OutSearch')->name('出库检索');
        Route::get('/out_detail', 'TaskController@OutDetail')->name('投料出库明细');
    });
    Route::get('/team', 'TeamController@lists')->name('班组建设');
    Route::get('/show', 'SettingController@show')->name('出库简介');
    Route::get('/insect_address', 'StockController@insectRecord')->name('虫群诱捕器位置');
});



Route::get('search/query', 'App\Http\Controllers\SearchController@query');
Route::get('search/add', 'App\Http\Controllers\SearchController@add');
Route::get('search/delete', 'App\Http\Controllers\SearchController@delete');
