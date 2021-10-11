<?php


namespace App\Modules\DigitalWin\EnShi\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function Test(){
        $user=DB::table('AREA')->get();
        dump($user);exit();
    }
}
