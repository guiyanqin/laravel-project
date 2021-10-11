<?php

use Illuminate\Support\Facades\Route;
use App\Modules\DigitalWin\EnShi\Controllers;
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
    return view('welcome');
});

Route::get('/user', function () {
    echo '你好，世界';
});





//调用控制器
Route::get('/user1', 'App\Http\Controllers\UserController@index');

use App\Modules\DigitalWin\EnShi\Controllers\UserController;

Route::get('/user2', [UserController::class, 'index']);

//可用的路由器方法
/*Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
*/

/*
$connnect = mysqli_connect('127.0.0.1', 'root', 'gyq258036', 'apidemo');
//查看扩展:phpinfo();
if (!$connnect) {
    exit('<h1>数据库连接失败</h1>');
} else {
    exit('<h1>数据库连接成功</h1>');
}
*/


