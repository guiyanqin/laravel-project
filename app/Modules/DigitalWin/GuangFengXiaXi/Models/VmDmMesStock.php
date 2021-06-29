<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Models;

use App\Models\ToBaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VmDmMesStock extends ToBaseModel
{

    private function defaultQuery(): Builder
    {
        //查找烟叶类型时统计 defaultQuery
        /**
        WHERE
        `WAREHOUSE_ID` = 672
        AND `BUILD_ID` IN ( 753, 752, 737 )
        AND `AREA_ID` != 1426
         **/
        return $this->newQuery()->where('WAREHOUSE_ID', '=', 672)
            //WAREHOUSE_ID=672是广丰下溪
            ->whereIn('BUILD_ID', [753,752,737])
            ->where('AREA_ID', '!=', '1426');
    }
    //库存总量
    public function getTotal(): int
    {
        $query = $this->defaultQuery();
        return $query->sum('AMOUNT');
    }

    //获取选项
    public function getOptions($arr = ['name'=>'TYPE', 'value'=>'TYPE'])
    {
        $select = [DB::raw("{$arr['name']} as name"),
            DB::raw("{$arr['value']} as value"),];
        return $this->defaultQuery()
            ->select($select)
            ->groupBy([$arr['name'], $arr['value']])
            ->orderBy($arr['name'], 'ASC')
            ->get();
    }

    //获取区域
    public function getAreas()
    {
        $select = [DB::raw('AREA_NAME as name'),
            DB::raw('AREA_ID as value')];
        return $this->defaultQuery()
            ->select($select)
            ->where('BUILD_ID', '=', '753')
            ->groupBy(['AREA_NAME', 'AREA_ID'])
            ->orderBy('AREA_NAME', 'ASC')
            ->get();
    }

    //货位信息查询栋ID、楼栋信息,可按年份筛选
    public function findByBuildId($buildId = '')
    {
        $query = $this->defaultQuery();
        if(!empty($buildId)){
            $query = $query->where('BUILD_ID', '=', $buildId);
        }
        return $query->groupBy('MATERIAL_YEAR')
            ->orderBy('LOCATION_NAME', 'ASC')
            ->get();
    }

    //物料检索
    public function search($where = []){
        $query = $this->defaultQuery()->where('WEIGHT', '>', '0');
        //YL_CODE：存货编码(原料中心)
        if(!empty($where['id'])){
            $query = $query->newQuery()
                ->where('YL_CODE','like','%'.$where['code'].'%');
        }
        //YL_DESC:存货名称
        if (!empty($where['name'])){
            $query = $query->newQuery()
                ->where('YL_DESC','like','%'.$where['name'].'%');
        }
        //AREA_ID层
        if(!empty($where['area_id'])){
            $query = $query->where('AREA_ID', '=', $where['area_id']);
        }
        //BUILD_ID栋
        if(!empty($where['build_id'])){
            $query = $query->where('BUILD_ID', '=', $where['build_id']);
        }
        //PLACE产地
        if(!empty($where['place'])){
            $query = $query->where('PLACE', '=', $where['place']);
        }
        //MATERIAL_YEAR烟叶年份
        if(!empty($where['year'])){
            $query = $query->where('MATERIAL_YEAR', '=', $where['year']);
        }
        //MATERIAL_YEAR起始年份
        if(!empty($where['start_year'])){
            if(empty($where['end_year'])){
                $query = $query->where('MATERIAL_YEAR', '>=', $where['start_year']);
            }else{
                $query = $query->whereBetween('MATERIAL_YEAR', [$where['start_year'],$where['end_year']]);
            }
        }

        //GRADESTRUCTUREDESC烟叶等级
        if(!empty($where['level'])){
            $query = $query->where('RADESTRUCTUREDESC', '=', $where['level']);
        }

        return $query->get();
    }

    //烟叶类型、形态、等级统计
    public function getStatistics($key = 'TYPE')
    {

        //TYPE=>GRADESTRUCTUREDESC()企业等级
        $select = [DB::raw("$key as name"),
            DB::raw("sum(WEIGHT) as total_weight"),
            DB::raw("sum(AMOUNT) as total_amount")];

        $query = $this->defaultQuery()->select($select);
        if(!empty($where)){
            $query = $query->where($where);
        }
        return $query->groupBy([$key])->get();
    }

    public function getShape($key = 'SHAPE')
    {

        //TYPE=>GRADESTRUCTUREDESC()企业等级
        $select = [DB::raw("$key as name"),
            DB::raw("sum(WEIGHT) as total_weight"),
            DB::raw("sum(AMOUNT) as total_amount")];

        $query = $this->defaultQuery()->select($select);
        if(!empty($where)){
            $query = $query->where($where);
        }
        return $query->groupBy([$key])->get();
    }

    //烟叶年份统计
    public function yearStatistics($key = 'TYPE', $value = ['WEIGHT'=>'weight','AMOUNT'=>'amount'], $where = [])
    {
        $select = [DB::raw("$key as name"),
            DB::raw("sum(WEIGHT) as total_weight"),
            DB::raw("sum(AMOUNT) as total_amount")];
        $query = $this->defaultQuery()->select($select);
        if(!empty($where)){
            $query = $query->where($where);
        }
        return $query->groupBy([$key])->get();
    }
}
