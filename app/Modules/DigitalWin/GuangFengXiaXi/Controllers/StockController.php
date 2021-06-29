<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Controllers;

use App\Helpers\Func;
use App\Http\Controllers\Controller;
use App\Models\ToBaseModel;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\YspylocationTomesViews;
use Illuminate\Http\Request;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\Location;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjInsectRecord;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjStorageIn;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjStorageOut;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmMesStock;


//库存
class StockController extends Controller
{
    private $stock;

    //__construct() 函数创建一个新的 SimpleXMLElement 对象。
    public function __construct(VmDmMesStock $stock)
    {
        $this->stock = $stock;
    }

    //库存总量
    public function getTotal()
    {
        $total = $this->stock->getTotal();
        Func::ajaxSuccess('', ['total'=>$this->myRound($total)]);
    }

    //获取层选项信息
    public function getArea(Request $request)
    {
        $result = [];
        $areaName = [ 'A' => '楼层一', 'B' => '楼层二', 'C' => '楼层三', 'D' => '楼层四', 'E' => '楼层五' ];
        $build = $request->get('build', 753);
        $type = $request->get('type', '1');
        $data = $this->stock->getAreas();
        foreach($data as $item){
            if($type == 1){
                $item->name = mb_substr($item->name, -2);
            }else{
                $item->name = $areaName[mb_substr($item->name, -2, 1)];
            }

        }
        if(!empty($data)){
            $result = $data->toArray();
        }

        Func::ajaxSuccess('', $result);
    }

    //获取烟叶等级、年份、产地、栋选项信息
    public function getOptions()
    {
        $typeArr = [
            'year' => ['name' => 'MATERIAL_YEAR', 'value'=> 'MATERIAL_YEAR'],   //烟叶年份
            'place' => ['name' => 'PLACE', 'value'=> 'PLACE'],     //烟叶产地
            'level' => ['name' => 'GRADESTRUCTUREDESC', 'value'=> 'GRADESTRUCTUREDESC'],//等级（等级结构）
            'type' => ['name' =>'TYPE','value'=>'TYPE'],
            'build' => ['name' => 'BUILD_NAME', 'value' => 'BUILD_ID'],   //栋
        ];
        $result = [];
        foreach($typeArr as $key => $arr) {
            $data = $this->stock->getOptions($arr);
            $result[$key] = $data->toArray();
        }
        Func::ajaxSuccess('', $result);
    }

    //货位信息，楼栋货位、可按年份筛选
    public function lineInfo(Request $request)
    {
        //数据获取
        $buildId = $request->get('build_id', '753');
        $itemName = $request->get('item_name', '');//item_name物料名称
        $itemCode = $request->get('item_code', '');//item_code物料编码
        $itemYear = $request->get('item_year', '');//item_year年份
        $itemPlace = $request->get('item_place', '');//产地
        $level = $request->get('level', '');//等级
        //findByBuildId查询栋ID
        $data = $this->stock->findByBuildId($buildId);


        $warehouse = [];    //库位数组

        //统计数组
        $statistics = [
            'total' => 0,    //总库位
            'empty_num' => 0, //空库位
            'item_num' => 0,  //已用库位
            'not_num' => 0,   //不可用库位
            'amount_total' => 0, //总件数
            'weight_total' => 0, //总重量
            'bear_total' => 0,  //总担数
        ];
        foreach($data as $item) {
            $key = $item->location_name;
            $num = (int)substr($key, 2);

            //仓位初始化
            if(empty($warehouse[$key[1]][$num])){
                $warehouse[$key[1]][$num] = ['location' =>$key, 'is_empty'=>true, 'item'=>[]];
                $statistics['total']++;
            }
            //不可用库位信息
            if($num > 1 && empty($warehouse[$key[1]][$num-1])){
                $warehouse[$key[1]][$num-1] = ['location' =>$key, 'is_empty'=>true, 'item'=>[]];
                $statistics['total']++;
                $statistics['not_num']++;
            }

            if($item->weight > 0){
                if(empty($warehouse[$key[1]][$num]['item'])){
                    $statistics['item_num']++;
                }
                $statistics['amount_total'] += $item->amount;
                $statistics['weight_total'] += $item->weight;

                //搜索状态：1、不是搜索结果，2.搜索结果
                $result = 1;
                //物料名称
                if( !empty($itemName) ){
                    if( !empty($item->yl_desc) && mb_strpos($item->yl_desc, $itemName) !== false ){
                        $result = 2;
                    }else{
                        $result = 1;
                    }
                }
                //物料编码
                if($result == 2 && !empty($itemCode)){
                    if( !empty($item->yl_code) && mb_strpos($item->yl_code, $itemCode) !== false ){
                        $result = 2;
                    }else{
                        $result = 1;
                    }
                }
                //物料年份
                if($result == 2 && !empty($itemYear) ){
                    if($item->material_year == $itemYear){
                        $result = 2;
                    }else{
                        $result = 1;
                    }
                }
                //产地
                if( $result == 2 && !empty($itemPlace) ){
                    if($item->place == $itemPlace){
                        $result = 2;
                    }else{
                        $result = 1;
                    }
                }
                //等级
                if( $result == 2 && !empty($level) ){
                    if($item->gradestructuredesc == $level){
                        $result = 2;
                    }else{
                        $result = 1;
                    }
                }

                //数据录入
                $warehouse[$key[1]][$num]['is_empty'] = false ;
                $warehouse[$key[1]][$num]['item'][]= [
                    'item_name' => $item->yl_desc,    //物料名称
                    'item_code' => $item->yl_code,    //物料编码
                    'location' => $key,               //库位
                    'place' => $item->place,          //产地
                    'year' => (int)$item->material_year,   //烟叶年份
                    'amount' => intval($item->amount),        //件数
                    'weight' => intval($item->weight),        //重量
                    'bear' => intval($item->weight/50),        //担数
                    'result' => $result,              //搜索结果
                ];
            }
        }
        //数据排序
        $info = [];
        foreach($warehouse as $key => $arr){
            $info[] = array_values($arr);
        }

        $statistics['empty_num'] = $statistics['total']-$statistics['item_num']-$statistics['not_num'];
        $statistics['bear_total'] = $this->myRound($statistics['weight_total']/50);
        $statistics['weight_total'] = $this->myRound($statistics['weight_total']);
        $statistics['amount_total'] = $this->myRound($statistics['amount_total']);
        $result = [
            'statistics' => $statistics,
            'list' => $info,
        ];

        //var_dump($pp);exit;
        Func::ajaxSuccess('', $result);
    }

    //烟叶类型统计
    public function typeStatistics()
    {
        $type_data = $this->stock->getStatistics();
        $shape_data = $this->stock->getShape();

        $type= [];
        $shape = [];
        foreach($type_data as $item){
            if(!empty($item->name)){
                $type = [
                    'name' => $item->name,
                    'total_weight' => $this->myRound($item->total_weight),
                    'total_amount'=>$this->myRound($item->total_amount)
                ];
            }
        }

        foreach($shape_data as $item){
            if(!empty($item->name)){
                $shape  = [
                    'name' => $item->name,
                    'total_weight' => $this->myRound($item->total_weight),
                    'total_amount'=>$this->myRound($item->total_amount)
                ];
            }
        }

        $result = [
            'type'=>$type,
            'shape' => $shape
        ];


        Func::ajaxSuccess('', $result);
    }

    //烟叶形态统计
    public function shapeStatistics(){
        $data = $this->stock->getShape();
        $result = [];
        foreach($data as $item){
            if(!empty($item->name)){
                $result[] = [
                    'name' => $item->name,
                    'total_weight' => $this->myRound($item->total_weight),
                    'total_amount'=>$this->myRound($item->total_amount)
                ];
            }
        }
        Func::ajaxSuccess('', $result);
    }

    //烟叶等级统计
    public function levelStatistics()
    {

        $data = $this->stock->getStatistics('GRADESTRUCTUREDESC');
        $result = [];
        foreach($data as $item){
            if(!empty($item->name)){
                $result[] = [
                    'name' => $item->name,
                    'total_weight' => $this->myRound($item->total_weight),
                    'total_amount'=>$this->myRound($item->total_amount)
                    //myRound()数组四舍五入
                ];
            }
        }

        Func::ajaxSuccess('', $result);
    }

    //烟叶年份统计
    public function yearStatistics(Request $request)
    {
        $build = $request->get('build');
        $where = [];

        $dataWeight = $this->stock->yearStatistics('MATERIAL_YEAR', 'WEIGHT', $where);//重量统计
        $dataAmount = $this->stock->yearStatistics('MATERIAL_YEAR', 'AMOUNT', $where);//件数统计
        $nowYear = (int)date('Y');//获取当前年份
        $year = ['red' => $nowYear - 9, 'yellow' => $nowYear - 7,'blue'=>$nowYear-5];
        if(!empty($build)){
            $where = ['BUILD_ID' => $build];
        }
        $result = [
            'red' => ['name' => $year['red'].'年以前', 'total_weight' => 0, 'total_amount' => 0],
            'yellow' => ['name' => $year['red'].'-'.$year['yellow'].'年'.'（不含）', 'total_weight' => 0, 'total_amount' => 0],
            'blue'=>['name'=>$year['yellow'].'-'.$year['blue'].'年'.'（不含）','total_weight'=>0,'total_amount'=>0],
            'green' => ['name' => $year['blue'].'-至今', 'total_weight' => 0, 'total_amount' => 0]
        ];
        //按年份进行重量统计处理
        foreach($dataWeight as $item){
            if($item->name < $year['red']){
                $result['red']['total_weight'] += $item->total_weight;
            }elseif ($item->name >= $year['red'] && $item->name < $year['yellow']){
                $result['yellow']['total_weight'] += $item->total_weight;
            }elseif ($item->name >= $year['yellow']  && $item->name < $year['blue'] ){
                $result['blue']['total_weight'] += $item->total_weight;
            }
            else{
                $result['green']['total_weight'] += $item->total_weight;
            }
        }
        //按年份进行件数统计处理
        foreach($dataAmount as $item){
            if($item->name < $year['red']){
                $result['red']['total_amount'] += $item->total_amount;
            }elseif ($item->name <= $year['yellow']){
                $result['yellow']['total_amount'] += $item->total_amount;
            }elseif ($item->name <= $year['blue']){
                $result['blue']['total_amount'] += $item->total_amount;
            }
            else{
                $result['green']['total_amount'] += $item->total_amount;
            }
        }
        foreach($result as $key => $val){
            $result[$key]['total_weight'] = $this->myRound($result[$key]['total_weight']);
            $result[$key]['total_amount'] = $this->myRound($result[$key]['total_amount']);
        }

        Func::ajaxSuccess('', $result);
    }

    //出入库记录
    public function getOrderLog(Request $request)
    {
        $len = $request->get('len', 3);
        $inData = (new VmDmGfgjStorageIn())->inLog($len);
        $outData = (new VmDmGfgjStorageOut())->outLog($len);

        $result = [];
        //var_dump($inData->toArray());exit;
        foreach($inData as $item){
            $number = strtotime($item->receipt_date).rand('0','9');
            $result[$number] = [
                'batch' => $item->bill_no,//批次号
                'status' => '入库',
                'amount' => $this->myRound($item->amount),//数量
                'weight' => $this->myRound($item->weight),//重量
                'location' => $item->goods_location_name,//库位
                'time' => substr($item->receipt_date, 5, 5)//时间
            ];
        }
        foreach($outData as $item){
            $number = strtotime($item->bill_date).rand('0','9');
            $result[$number] = [
                'batch' => $item->bill_no,
                'status' => '出库',
                'amount' => $this->myRound($item->amount),
                'weight' => $this->myRound($item->weight),
                'location' => $item->goods_location_name,
                'time' => substr($item->bill_date, 5, 5)
            ];
        }
        krsort($result);
        $result = array_values($result);

        Func::ajaxSuccess('', $result);
    }

    //物料检索
    public function search(Request $request)
    {
        $where = [
            //trim() 函数移除字符串两侧的空白字符或其他预定义字符。
            'name' => trim($request->get('name', '')),
            'code' => trim($request->get('code', '')),
            'build' => (int)$request->get('build', 0),
            'area' => (int)$request->get('area', 0),
            'level' => trim($request->get('level', '')),
            'place' => trim($request->get('place', '')),
            'year' => trim($request->get('year', '')),
            'start_year' => (int)$request->get('start_year'),
            'end_year' => (int)$request->get('end_year'),
        ];
        $data = $this->stock->search($where);

        $info = [];
        $statistics = [
            'amount_total' => 0, //总件数
            'weight_total' => 0, //总重量
            'bear_total' => 0,  //总担数
        ];
        //遍历给定的 data 数组。每次循环中，当前单元的值被赋给 $item 并且数组内部的指针向前移一步（因此下一次循环中将会得到下一个单元）。
        foreach($data as $item) {
            $info[$item->location_name] = [
                'item_name' => $item->yl_desc,               //物料名称
                'item_code' => $item->yl_code,               //物料编码
                'location' => $item->location_name,          //库位
                'place' => $item->place,                     //产地
                'year' => $item->material_year,              //烟叶年份
                'amount' => intval($item->amount),           //件数
                'weight' => intval($item->weight),           //重量
                'bear' => intval($item->weight/50),      //担数
                'level' => $item->gradestructuredesc,        //等级
                'type' => $item->type,                       //烟叶类型
                'shape' => $item->shape,                     //烟叶形态
                'qc_status' => $item->qc_status == 1?'合格':'不合格',       //质检状态
            ];
            $statistics['amount_total'] += $item->amount;
            $statistics['weight_total'] += $item->weight;
        }
        $statistics = [
            'amount_total' => $this->myRound($statistics['amount_total']), //总件数
            'weight_total' => $this->myRound($statistics['weight_total']), //总重量
            'bear_total' => $this->myRound($statistics['weight_total']/50),  //总担数
        ];

        ksort($info);
        $info = array_values($info);

        $result = [
            'statistics' => $statistics,
            'list' => $info
        ];

        Func::ajaxSuccess('', $result);
    }

    //周转率
    public function getTurnoverRate(Request $request)
    {
        $arr = [
            'day' => ['format'=>'m-d', 'len' => 7],
            'month' => ['format'=>'Y-m', 'len' => 365]
        ];
        $type = $request->get('type', 'day');
        $total = round($this->stock->getTotal('WEIGHT'));
        $inData = (new VmDmGfgjStorageIn())->inLog($arr[$type]['len']);
        $outData = (new VmDmGfgjStorageOut())->outLog($arr[$type]['len']);
        //var_dump($inData->toArray());exit;

        $result = [];
        foreach($outData  as $item){
            $date = date($arr[$type]['format'],strtotime($item->bill_date));
            if(empty($result[$date])) {
                $result[$date] = ['name' => $date, 'value' => 0, 'num' => 0, 'total' => $total];
            }
            $result[$date]['num'] += $item->weight;
            $result[$date]['value'] = round($result[$date]['num'] / $total * 100, 2);
        }
        sort($result);
        Func::ajaxSuccess('', $result);
    }

    //虫群诱捕器位置
    public function insectRecord()
    {
        $data = (new VmDmGfgjInsectRecord())->getData();

        $result = [];
        foreach($data as $item){
            $result[] = $item->goods_location_name;
        }

        sort($result);

        Func::ajaxSuccess('', $result);
    }

    //数组四舍五入
    private function myRound($total): int
    {
        return intval($total);
    }

}
