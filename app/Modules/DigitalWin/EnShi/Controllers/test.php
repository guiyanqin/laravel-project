<?php


namespace App\Modules\DigitalWin\EnShi\Controllers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class test
{
    public function test()
    {
        return DB::connection('oracle')->table('user')->limit(10)->get();
    }
}
