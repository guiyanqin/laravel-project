<?php

namespace App\Modules\DigitalWin\EnShi\Models;

use App\Models\ToBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Result;
use mysqli;

class VmDmGfgjWsd extends ToBaseModel
{
    use HasFactory;
    // TEMPREALVALUE:温度，HUMIREALVALUE：湿度，GETDATE：采集时间，DEVICETYPE：设备类型
    public function getData($areaId, $startDate, $endDate){
        $select =['DEVICETYPE','TEMPREALVALUE','HUMIREALVALUE'];
        return $this->newQuery()
            ->select($select)
            ->where('DEVICETYPE','=','4')
            ->where('LOCATIONNAME','=',$areaId)
            ->whereBetween('GETDATE', [$startDate, $endDate])
            ->get();
    }

    //当前温湿度
    public function getAmbientData($build,$layer)
    {
        //GETDATE:采集时间
        $query = $this->newQuery()
            ->where('DEVICETYPE', '=', '4')
        ;
        //栋ID
        if(!empty($build)){
            $query = $query->where('BUILDINGID', '=', $build);
        }
        return $query->orderBy('GETDATE', 'DESC')->get();
    }


    //历史温湿度
    public function getAmbientLogData($area_id, $start_date, $end_date)
    {
        //TEMPREALVALUE:温度，HUMIREALVALUE：湿度，GETDATE：采集时间，DEVICETYPE：设备类型
        $query = $this->newQuery()
            ->where('DEVICETYPE', '=', '4');
        return $query
            ->orderBy('GETDATE', 'ASC')
            ->get();
    }
    //包芯温度
    public function getCoreTemperature($area_id = 0, $timeStr = ''){
        $select = ['LOCATIONNAME', DB::raw('count(TAGID) as num'), DB::raw('sum(TEMPREALVALUE) as total')];
        return $this->newQuery()
            //DB::raw()把查询的结果集当成一个临时表，然后在使用laravel的查询构造器语法进行分页处理；
            ->select($select)
            ->where('DEVICETYPE','=','4')
            ->where('GETDATE','>',$timeStr)
            ->get();
        //AREAID:区ID
        if(!empty($area_id)){
            $query = $query->where('AREAID', '=', $area_id);
        }
        return $query->groupBy(['LOCATIONNAME'])->get();
    }

    public function getCladdingData($areaId = 0, $timeStr = '')
    {

        $select = ['TAGID','GETDATE','TEMPREALVALUE','AREAID','LOCATIONNAME'];
        $query = $this->newQuery()
            ->from('vm_dm_gfgj_wsd as v')
            ->select($select)
            ->whereNotExists(function($query)
            {
                $len = 3;
                $timeStr = date("Y-m-d", strtotime("-".$len." days"));
                $query->from('vm_dm_gfgj_wsd')
                    ->select(DB::raw(1))
                    ->where('GETDATE','<',$timeStr)
                    ->where('AREAID','=','v.AREAID')
                    ->get()
                ;
            })
            ->where('DEVICETYPE','!=','4')
            ->groupBy('LOCATIONNAME');
        return $query->orderBy('GETDATE','DESC')->get();

    }

    public function getCoreTemperatureData($areaId = 0, $timeStr = ''): array
    {

        return DB::select('SELECT TAGID as tagid,GETDATE as get_date,TEMPREALVALUE as tempreavalue,AREAID as area_id,LOCATIONNAME as locationname FROM vm_dm_gfgj_wsd v WHERE GETDATE IN ( SELECT MAX(GETDATE) FROM vm_dm_gfgj_wsd GROUP BY AREAID HAVING COUNT( * ) > 1 ) AND DEVICETYPE != 4');
    }

    public function getGas($areaId = 0, $timeStr = ''){
        $select = ['TAGID','GETDATE','TEMPREALVALUE','AREAID','LOCATIONNAME'];
        $query = $this->newQuery()
            ->from('vm_dm_gfgj_wsd as v')
            ->select($select)
            ->whereNotExists(function($query)
            {
                $len = 3;
                $timeStr = date("Y-m-d", strtotime("-".$len." days"));
                $query->from('vm_dm_gfgj_wsd')
                    ->select(DB::raw(1))
                    ->where('GETDATE','<',$timeStr)
                    ->where('AREAID','=','v.AREAID')
                    ->get()
                ;
            })
            ->where('DEVICETYPE','!=','4')
            ->groupBy('LOCATIONNAME');
        return $query->orderBy('GETDATE','DESC')->get();

    }


}
