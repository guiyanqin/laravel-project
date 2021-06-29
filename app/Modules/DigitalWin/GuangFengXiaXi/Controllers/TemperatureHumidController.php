<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Controllers;
use App\Http\Controllers\Controller;
use App\Helpers\Func;
use App\Modules\DigitalWin\GuangFengXiaXi\Models\VmDmGfgjWsd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


//温湿度监控
class TemperatureHumidController extends Controller
{
    private $model;
    private $state = ['1'=>'使用中','2'=>'未使用'];
    private $area_name = ['A'=>'楼层1','B'=>'楼层2','C'=>'楼层3','D'=>'楼层4','E'=>'楼层5'];
    private $areaId;

    public function __construct(VmDmGfgjWsd $model)//显示声明一个构造方法且带参数
    {
        $this->model = $model;
    }


    //当前温湿度
    public function now(Request $request)
    {
        //传入数据
        $build = $request->get('build', 753);
        $layer = $request->get('layer', 785);   //层
        //获取数据处理
        $data = $this->model->getAmbientData($build,$layer);
       // var_dump($data->toArray());exit;

        //数据返回模版
        $result = [];
        foreach($data as $item){
            $key = mb_substr($item->areaname, -2, 1);
            //mb_substr() 函数返回字符串的一部分
            //substr() 函数，它只针对英文字符，如果要分割的中文文字则需要使用 mb_substr()
            if(empty($result[$key])){
                $result[$key] = [
                    'id' => $item->areaid,
                    'name' => $this->area_name[$key],
                    'area' => [],
                ];
            }
            if(empty($result[$key]['area'][$item->locationid])){
                $item->devicestate = (int)$item->devicestate;//设备状态
                //详细信息
                $result[$key]['area'][$item->locationid] = [
                    'id' => $item->locationid,
                    'name' => $item->locationname,
                    'temperature' => round($item->temprealvalue,2),
                    't_status' => empty($this->state[$item->devicestate])?$item->devicestate:$this->state[$item->devicestate],
                    'humidity' => round($item->humirealvalue,2),
                    //round()对浮点数进行四舍五入
                    'h_status'=> empty($this->state[$item->devicestate])?$item->devicestate:$this->state[$item->devicestate],
                ];
            }

        }
        $info = [
            'id' => -1,
            'name' => "空",
            'temperature' => -1,
            't_status' => '状态异常',
            'humidity' => -1,
            'h_status'=> '状态异常',
        ];
        foreach($this->area_name as $key => $val){
            if(empty($result[$key])){
                $result[$key] = [
                    'id' => 0,
                    'name' => $val,
                    'area' => [
                        $info,$info
                    ],
                ];

            }
            $result[$key]['area']= array_values($result[$key]['area']);
        }

        ksort($result);
        //使用 krsort() 函数对关联数组按照键名进行排序,。
        //使用 asort() 函数对关联数组按照键值进行排序。
        $result = array_values($result);
        //array_values() 函数返回包含数组中所有的值的数组。

        Func::ajaxSuccess('', $result);
    }

    //历史温湿度
    public function history(Request $request)
    {
        //传入数据

        $area = $request->get('area', 1);    //区域

        $startDate = $request->get('start_date', date('Y-m-d')).' 00:00:00'; //开始时间
        $endDate = $request->get('end_date', date('Y-m-d')).' 23:59:59';   //结束时间
        /*$startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $day = round(($endTime-$startTime)/86400)-1;*/
        //var_dump($day);exit;

        //数据获取与处理
        $data = $this->model->getAmbientLogData($area, $startDate, $endDate);

        //数据返回模版
        $result = ['temperature' => [], 'humidity' => []];
        foreach($data as $item){
            $name = date("Y年m月d日H时", strtotime($item->getdate));
            $result['temperature'][] = [
                'name' => $name,
                'value' => $item->temprealvalue
            ];
            $result['humidity'][] = [
                'name' => $name,
                'value' => $item->humirealvalue
            ];
        }

        Func::ajaxSuccess('', $result);
    }

    //包芯温度
    public function cladding(Request $request)
    {
        //传入数据

        $build = $request->get('build', 753);   //栋
        $layer = $request->get('layer', 785);   //层
        $timeStr = date('Y-m-d h:i:s');
        $getDate = $request->get('get_date');
        //获取数据
        $data = $this->model->getCoreTemperatureData($build, $timeStr);
//        var_dump($data['tagid']);exit;


        //输出数据

        $result = [];
        foreach($data as $item) {
            $key = mb_substr($item->area_id, -2, 1);
            if(empty($result[$key])){
                $result[] = [
                    'id' => $item->area_id,
                    'name' => $item->locationname,
                    'temperature' => round($item->tempreavalue, 2),
                    't_status' => '使用中',
                    'get_date' => $item->get_date
                ];
            }
        }
        Func::ajaxSuccess('', $result);
    }

    public function claddingDate(Request $request)
    {
        //传入数据

        $build = $request->get('build', 753);   //栋
        $layer = $request->get('layer', 785);   //层
        $timeStr = date('Y-m-d h:i:s');

        $data = $this->model->getCladdingData($build, $timeStr);
        //var_dump($data->toArray());exit();
        $result = [];
        foreach($data as $item){
            $result[] = [
                'name' => $item->locationname,
                'temperature_value' => $item->temprealvalue,
                't_status' => '使用中',
                'date' => $item->getdate,
            ];
        }

        Func::ajaxSuccess('', $result);

    }


}
