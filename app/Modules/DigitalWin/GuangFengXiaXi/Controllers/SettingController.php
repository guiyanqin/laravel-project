<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Controllers;

use App\Helpers\Func;
use App\Http\Controllers\Controller;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\Setting;

use Illuminate\Http\Request;

//仓库检测
class SettingController extends Controller
{
    private $model;

    public function __construct(Setting $model)
    {
        $this->model = $model;
    }

    //列表
    public function lists(Request $request)
    {
        $data = $this->model->getAll(['option', 'value']);
        $result = [];
        foreach($data as $item){
            $result[$item['option']] = $item['value'];
        }

        Func::ajaxSuccess('',$result);
    }

    //添加
    public function create(Request $request)
    {
        $data = $request->input();
        if(!empty($data)){
            foreach($data as $key => $val){
                if($key == 'introduction'){
                    $create = [
                        'value' => $val,
                        'user' => auth()->user()->username
                    ];
                    $this->model->create('introduction', $create);
                }
            }
        }

        Func::ajaxSuccess('');
    }


    //仓库简介
    public function show()
    {
        $data = $this->model->findByOption('introduction');
        $result = [
            'content' => $data['value']
        ];

        Func::ajaxSuccess('', $result);
    }
}
