<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Controllers;

use App\Helpers\Exceptions\ControllerException;
use App\Helpers\Func;
use App\Http\Controllers\Controller;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\Options;

use Illuminate\Http\Request;

//含水率检测
//烟叶形态、年份、结构的增删改查
class OptionsController extends Controller
{
    private $model;
    private $typeName = [
        '1' => '烟叶形态',
        '2' => '烟叶年份',
        '3' => '等级结构'
    ];

    public function __construct(Options $team)
    {
        $this->model = $team;
    }

    //列表（根据关键字查询）
    public function lists(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $type = $request->get('type', 0);
        $size = $request->get('size', '10');
        $data = $this->model->findFrom($keyword, $type, $size);
        foreach($data as $item){
            $item->typeName = $this->typeName[$item->type];
        }

        $data = $this->getPageList($data);
        //getPageList()分页格式化

        Func::ajaxSuccess('',$data);
    }

    //添加
    public function add(Request $request)
    {
        $data = $request->input();
        $rule = [
            'name' => ['required'],
            'type' => ['required', 'numeric']
        ];
        $messages = [
            'name.required' => "名称不能为空",
            'type.required' => "选项类型不能为空",
            'type.numeric' => "选项类型必须为数字",
        ];
        try {
            if ($this->verification($data, $rule, $messages)) {
                $insert = [
                    'name' => $data['name'],
                    'type' => (int)$data['type'],
                    'user' => auth()->user()->username,
                ];
                $this->model->create($insert);
                Func::ajaxSuccess('', '操作成功');
            }
        } catch (ControllerException $e) {
        }
    }

    //修改
    public function edit(Request $request)
    {
        $data = $request->input();
        $rule = [
            'id' => ['required', 'numeric'],
            'name' => ['required'],
            'type' => ['required', 'numeric']
        ];
        $messages = [
            'id.required' => "id不能为空",
            'id.numeric' => "id必须为数字",
            'name.required' => "名称不能为空",
            'type.required' => "选项类型不能为空",
            'type.numeric' => "选项类型必须为数字",
        ];
        try {
            if ($this->verification($data, $rule, $messages)) {
                $update = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'type' => (int)$data['type'],
                    'user' => auth()->user()->username,
                ];
                $this->model->create($update);
                Func::ajaxSuccess('', '操作成功');
            }
        } catch (ControllerException $e) {
        }
    }

    //删除
    public function delete(Request $request)
    {
        //输入数据
        $data = $request->input();
        $rule = [
            'id' => ['required', 'numeric'],
        ];
        $messages = [
            'id.required' => "id不能为空",
            'id.numeric' => "id必须为数字",
        ];
        try {
            if ($this->verification($data, $rule, $messages)) {
                $team = $this->model->findById($data['id']);
                if (!empty($team)) {
                    $team->delete();
                }
                Func::ajaxSuccess('删除成功');
            }
        } catch (ControllerException $e) {
        }
    }

    //分类选项（烟叶类型）
    public function getType()
    {
        $result = [];
        foreach($this->typeName as $key => $val){
            $result[] = [
                'name' => $val,
                'value' => $key
            ];
        }
        Func::ajaxSuccess('', $result);
    }
}
