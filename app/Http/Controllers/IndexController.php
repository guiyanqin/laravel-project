<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TestUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class IndexController extends Controller
{
    //

    public function index(){
        return view('test.index');
    }


}
