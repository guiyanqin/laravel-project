<?php

namespace App\Modules\DigitalWin\EnShi\Controllers;

use App\Helpers\Exceptions\ControllerException;

use App\Helpers\Func;
use App\Http\Controllers\Controller;
use App\Modules\DigitalWin\EnShi\Models\ItemMoisture;
use App\Modules\DigitalWin\EnShi\Models\ItemMoistureLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * 含水量管理
 * @package App\Modules\Digitaltwin\Guangfeng\Controllers
 */
class QualityController extends Controller
{
    private $model;

    public function __construct(ItemMoisture $model)
    {
        $this->model = $model;
    }

    //列表
    public function lists(Request $request)
    {
        $size = $request->get('size', '10');
        $where = [
            'batch' => $request->get('batch', ''),
            'shape_id' => $request->get('shape_id', 0),
            'year_id' => $request->get('year_id', 0),
            'level_id' => $request->get('level_id', 0),

        ];
        $data = $this->model->findFrom($where, $size);

        foreach($data as $item){
            $item->test_date = date("Y-m-d", $item->test_date);
        }

        $data = $this->getPageList($data);

        Func::ajaxSuccess('',$data);
    }

    //添加
    public function add(Request $request)
    {
        $data = $request->input();
        $rule = [
            'batch' => ['required'],
            'ratio' => ['required', 'numeric'],
            'test_date' => ['required', 'date'],
            'shape_id' => ['required', 'numeric'],
            'year_id' => ['required', 'numeric'],
            'level_id' => ['required', 'numeric'],
        ];
        $messages = [
            'batch.required' => "批次号不能为空",
            'ratio.required' => "含水率不能为空",
            'ratio.numeric' => '含水率必须为数字',
            'test_date.required' => "监测日期不能为空",
            'test_date.date' => '监测日期格式错误',
            'shape_id.required' => "烟叶形态不能为空",
            'shape_id.numeric' => '烟叶形态ID必须为数字',
            'year_id.required' => "烟叶年份不能为空",
            'year_id.numeric' => '烟叶年份ID必须为数字',
            'level_id.required' => "等级结构不能为空",
            'level_id.numeric' => '等级结构ID必须为数字',
        ];
        if($this->verification($data, $rule, $messages)){
            $moisture = $this->model->findByBatch($data['batch']);
            if(!empty($moisture)) {
                throw new ControllerException('批次号已存在');
            }
            $time = time();
            $insert = [
                'batch' => $data['batch'],
                'ratio' => $data['ratio'],
                'test_date' => strtotime($data['test_date']),
                'shape_id' => $data['shape_id'],
                'year_id' => $data['year_id'],
                'level_id' => $data['level_id'],
                'user' => auth()->user()->username,
                'created_at' => $time,
                'updated_at' => $time
            ];
            $this->create($insert);
            Func::ajaxSuccess('', '操作成功');
        }
    }

    //修改
    public function edit(Request $request)
    {
        $data = $request->input();
        $rule = [
            'id' => ['required', 'numeric'],
            'batch' => ['required'],
            'ratio' => ['required', 'numeric'],
            'test_date.required' => "监测日期不能为空",
            'test_date.date' => '监测日期格式错误',
            'shape_id' => ['required', 'numeric'],
            'year_id' => ['required', 'numeric'],
            'level_id' => ['required', 'numeric'],
        ];
        $messages = [
            'id.required' => 'id不能为空',
            'batch.required' => "批次号不能为空",
            'ratio.required' => "含水率不能为空",
            'ratio.numeric' => '含水率必须为数字',
            'test_date.required' => "监测日期不能为空",
            'test_date.date' => '监测日期格式错误',
            'shape_id.required' => "烟叶形态不能为空",
            'shape_id.numeric' => '烟叶形态ID必须为数字',
            'year_id.required' => "烟叶年份不能为空",
            'year_id.numeric' => '烟叶年份ID必须为数字',
            'level_id.required' => "等级结构不能为空",
            'level_id.numeric' => '等级结构ID必须为数字',
        ];
        if($this->verification($data, $rule, $messages)){
            $moisture = $this->model->findByBatch($data['batch'], $data['id']);
            if(!empty($moisture)) {
                throw new ControllerException('批次号已存在');
            }
            $update = [
                'batch' => $data['batch'],
                'ratio' => $data['ratio'],
                'test_date' => strtotime($data['test_date']),
                'shape_id' => $data['shape_id'],
                'year_id' => $data['year_id'],
                'level_id' => $data['level_id'],
                'user' => auth()->user()->username,
                'updated_at' => time()
            ];
            $quality = $this->model->findById($data['id']);
            if(empty($quality)) {
                throw new ControllerException('对象不存在');
            }
            $this->create($update, $data['id']);
            Func::ajaxSuccess('', '操作成功');
        }
    }

    //删除
    public function delete(Request $request)
    {
        $data = $request->input();
        $rule = [
            'id' => ['required', 'numeric'],
        ];
        $messages = [
            'id.required' => "id不能为空",
            'id.numeric' => "id必须为数字",
        ];
        if($this->verification($data, $rule, $messages)){
            $quality = $this->model->findById($data['id']);
            if(!empty($quality)){
                $quality->delete();
            }
            Func::ajaxSuccess('删除成功');
        }
    }

    //日志
    public function log(Request $request)
    {
        $batch = $request->get('batch', '');
        $size = $request->get('size', '10');
        $data = (new ItemMoistureLog())->findByBatch($batch, $size);
        foreach($data as $item){
            $item->test_date = date('Y-m-d', $item->test_date);
        }

        $data = $this->getPageList($data);

        Func::ajaxSuccess('',$data);
    }

    /**
     * 录入信息
     * @param $data
     * @param int $id
     * @return bool
     * @throws ControllerException
     */
    private function create($data, $id = 0)
    {
        $status = true;
        if(empty($id)){
            $quality = $this->model;
        }else{
            $quality = $this->model->findById($id);
            //批次号或含水率没改变时不用写日志
            if($quality->batch == $data['batch'] && $quality->ratio == $data['ratio'] && $quality->test_date == $data['test_date']) {
                $status = false;
            }
        }
        DB::beginTransaction();
        try {
            //满足条件
            if( $status ){
                //添加日志信息
                $log = new ItemMoistureLog();
                $log->setAttribute('batch', $data['batch']);
                $log->setAttribute('ratio', $data['ratio']);
                $log->setAttribute('test_date', $data['test_date']);
                $log->setAttribute('user', $data['user']);
                $log->save();
            }

            //录入当前信息
            foreach($data as $key => $val){
                $quality->setAttribute($key, $val);
            }
            $quality->save();
            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
            throw new ControllerException('更新失败'.$e->getMessage());
        }
        return true;
    }
}
