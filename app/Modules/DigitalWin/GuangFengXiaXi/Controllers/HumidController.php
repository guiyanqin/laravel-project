<?php

namespace App\Http\Controllers;

use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjWsd;
use Illuminate\Http\Request;

/*class HumidController extends Controller
{
    private $model;
    private $state = ['0'=>'未使用','1'=>'使用中'];
    private $floor = ['A'=>'楼层1','B'=>'楼层2','C'=>'楼层3','D'=>'楼层4','E'=>'楼层5'];

    public function __construct(VmDmGfgjWsd $model)
    {
        $this->model = $model;
    }

    //当前温度
    public function now(Request $request){
        // 1 传入数据
        $build = $request->get('build','753');//栋
        $layer = $request->get('layer');  //层
        $area = $request->get('area', 1);    //区域
        // 2 获取数据
        $data = $this->model->getAmbientData($build);
        // 3 返回数据
        $result = [];
        foreach($data as $item){

        }

    }

    //历史温度
    public function history(Request $request){
        // 1 传入数据
        $build = $request->get('build','753');//栋
        $layer = $request->get('layer');  //层
        $area = $request->get('area', 1);    //区域
        $startDate = $request->get('start_date',date('Y-m-d')).' 00:00:00';
        $endDate = $request->get('end_date',date('Y-m-d')).' 23:59:59';
        // 2 获取数据
        $data = $this->model->getAmbientLogData($area, $startDate, $endDate);
        // 3 返回数据
        $result = ['temperature' => [], 'humidity' => []];

    }

    //包芯温度
    public function cladding(Request $request){
        // 1 传入数据

        // 2 获取数据
        // 3 返回数据

    }

}11
