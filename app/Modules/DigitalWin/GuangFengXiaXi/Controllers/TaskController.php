<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Controllers;

use App\Helpers\Func;
use App\Http\Controllers\Controller;
use App\Models\ToBaseModel;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjStorageIn;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjStorageInItem;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjStorageOut;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjStorageOutItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

//出入库
class TaskController extends Controller
{
    private $inModel;
    private $outModel;
    //auditName审核状态
    private $auditName = [
        '-2' => '待提交审核',
        '-1' => '未通过',
        '0' => '未处理',
        '1' => '通过',
    ];
    //billName表单名称
    private $billName = [
        '1' => 'pda未下载',
        '2' => 'pda已下载',
        '3' => 'pda已处理',
        '4' => 'pda撤回',
        '5' => '草稿',
    ];

    public function __construct(VmDmGfgjStorageIn $inModel, VmDmGfgjStorageOut $outModel)
    {
        //inModel输入模型，outModel输出模型
        $this->inModel = $inModel;
        $this->outModel = $outModel;
    }


    //获取入库检索选项
    public function getInOptions(Request $request)
    {
        $type = $request->get('type', '');
        $typeArr = [
            'name' => ['name' => 'REALNAME', 'value' => 'REALNAME'],   //收货人
            'type' => ['name' => 'FDESC', 'value' => 'SEND_RECEIVE_TYPE'],   //收货类别
            'bill' => ['name' => 'BILL_STATUS', 'value' => 'BILL_STATUS'],     //单据状态
            'audit' => ['name' => 'AUDIT_STATUS', 'value' => 'AUDIT_STATUS'],  //审核状态
            'build' => ['name' => 'STORE_HOUSE_NAME', 'value' => 'STORE_HOUSE_ID'],   //栋
        ];
        $result = [];
        foreach ($typeArr as $key => $arr) {
            if (!empty($type) && $key != $type) continue;
            $data = $this->inModel->getOptions($arr);
            if ($key == 'build') {
                foreach ($data as $item) {
                    $item->name = str_replace(['下溪库', '#'], ['', '栋'], $item->name);
                }
            }
            if ($key == 'bill') {
                foreach ($data as $item) {
                    if (!empty($this->billName[$item->name])) {
                        $item->name = $this->billName[$item->name];
                    }
                }
            }
            if ($key == 'audit') {
                foreach ($data as $item) {
                    if (!empty($this->auditName[$item->name])) {
                        $item->name = $this->auditName[$item->name];
                    }
                }
            }

            $result[$key] = $data->toArray();
        }
        Func::ajaxSuccess('', $result);
    }


     //入库物料检索

    public function inSearch(Request $request)
    {
        $where = [
            //trim()移除字符串两侧的字符
            'name' => trim($request->get('name', '')),    //收货人员
            'code' => trim($request->get('code', '')),    //单据编号
            'build' => (int)$request->get('build', 0),    //栋
            'type' => (int)$request->get('type', 0),      //收货类别
            'bill' => trim($request->get('bill', 0)),   //单据状态
            'audit' => trim($request->get('audit', 0)),  //审核状态
            'start_date' => $request->get('start_date', date('Y-m-d', strtotime("- 7 days"))),  //收货时间开始
            'end_date' => $request->get('end_date'),  //收货时间结束
        ];
        //数据获取
        $data = $this->inModel->search($where);

        $info = [];
        $weightTotal = 0;  //总重量
        foreach ($data as $item) {
            $info[] = [
                'id' => $item->id,
                'bill_no' => $item->bill_no,                  //单据编号
                'realname' => $item->realname,               //收货人
                'receipt_date' => substr($item->receipt_date, 0, 10),          //收货日期
                'store_region_name' => $item->store_region_name,                     //收货仓库
                'store_house_name' => $item->store_house_name,              //栋
                'fdesc' => $item->fdesc,           //收发类别名称
                'bill' => empty($this->billName[$item->bill_status])?$item->bill_status:$this->billName[$item->bill_status], //单据状态
                'shape' => empty($this->auditName[$item->audit_status])?$item->audit_status:$this->auditName[$item->audit_status],//审核状态
                'qc_status' => $item->qc_status == 1 ? '合格' : '不合格',       //质检状态
            ];
            $weight  = (new VmDmGfgjStorageInItem())->getTotal('WEIGHT', ['IN_ID' => $item->id]);
            $weightTotal += $weight ;
        }

        $result = [
            'weight_total' => round($weightTotal),
            'list' => $info
        ];

        Func::ajaxSuccess('', $result);
    }


      //入库详情

    public function inDetail(Request $request)
    {
        $inId = (int)$request->get('in_id');

        $data = (new VmDmGfgjStorageInItem())->getData($inId);

        foreach ($data as $item){
            $item->amount = round($item->amount, 2);
            $item->weight = round($item->weight, 2);
        }

        Func::ajaxSuccess('', $data);
    }

    // 获取出库检索选项

    public function getOutOptions(Request $request)
    {
        $type = $request->get('type', '');
        $typeArr = [
            'name' => ['name' => 'REALNAME', 'value' => 'REALNAME'],   //库管员
            'bill' => ['name' => 'STATUS', 'value' => 'STATUS'],     //单据状态
            'tobacco_grade' => ['name' => 'TOBACCO_GRADE', 'value' => 'TOBACCO_GRADE'],  //烟丝牌号
        ];
        $result = [];
        foreach ($typeArr as $key => $arr) {
            if (!empty($type) && $key != $type) continue;
            if($key == 'tobacco_grade'){
                $data = (new VmDmGfgjStorageOutItem())->getOptions($arr);
            }else{
                $data = $this->outModel->getOptions($arr);
            }

            if ($key == 'bill') {
                foreach ($data as $item) {
                    if (!empty($this->billName[$item->name])) {
                        $item->name = $this->billName[$item->name];
                    }
                }
            }

            $result[$key] = $data->toArray();
        }
        Func::ajaxSuccess('', $result);
    }


   //出库物料检索

    public function outSearch(Request $request)
    {
        $where = [
            'name' => trim($request->get('name', '')),    //库管员
            'code' => trim($request->get('code', '')),    //单据编号
            'bill' => $request->get('bill', 0),   //单据状态
            'grade' => trim($request->get('grade', '')), //烟丝牌号
            'start_date' => $request->get('start_date', date('Y-m-d', strtotime("- 7 days"))),  //时间开始
            'end_date' => $request->get('end_date'),  //时间结束
        ];
        $data = $this->outModel->search($where);
        //var_dump($data->toArray());exit;

        $info = [];
        $weightTotal = 0;  //总重量
        foreach ($data as $item) {
            if(empty($info[$item->id])){
                $info[$item->id] = [
                    'id' => $item->id,
                    'bill_no' => $item->bill_no,                  //单据编号
                    'realname' => $item->realname,               //收货人
                    'bill_date' => substr($item->bill_date, 0, 10),          //收货日期
                    'out_repository_name' => $item->out_repository_name,                     //收货仓库
                    'fdesc' => $item->fdesc,           //收发类别
                    'bill' => empty($this->billName[$item->status])?$item->status:$this->billName[$item->status], //单据状态
                    'tobacco_grade' => $item->tobacco_grade,   //烟丝牌号
                    'batch_no' => $item->batch_no    //批次号
                ];
            }

            $weightTotal += $item->weight;
        }
        $info = array_values($info);

        $result = [
            'weight_total' => round($weightTotal),
            'list' => $info
        ];

        Func::ajaxSuccess('', $result);
    }

  //入库详情

    public function outDetail(Request $request)
    {
        $outId = (int)$request->get('out_id');

        $data = (new VmDmGfgjStorageOutItem())->getData($outId);

        foreach ($data as $item){
            $item->amount = round($item->amount, 2);
            $item->weight = round($item->weight, 2);
        }

        Func::ajaxSuccess('', $data);
    }
}


