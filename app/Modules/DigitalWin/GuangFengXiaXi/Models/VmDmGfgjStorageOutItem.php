<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Models;

use App\Models\ToBaseModel;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;

class VmDmGfgjStorageOutItem extends ToBaseModel
{
    //use HasFactory;
    //输出name值不为空的名称和值
    public function getOptions($arr = ['name'=>'TOBACCO_GRADE', 'value'=>'TOBACCO_GRADE']){
        return $this->newQuery()
            ->select([$arr['name']. ' as name', $arr['value']. ' as value'])
            ->whereNotNull($arr['name'])
            ->groupBy([$arr['name'], $arr['value']])
            ->orderBy($arr['name'], 'ASC')
            ->get();
    }
    //统计weight重量
    public function getTotal($key = 'weight', $where = []){
        $query = $this->newQuery();
        if(!empty($where)){
            $query = $query->where($where);
        }
        return $query->sum($key);
    }
    //统计出库数据详情信息
    public function getData($outId)
    {
        $select = ['INVENTORY_CODE as name', 'TOBACCO_YEAR_CODE as year', 'GOODS_LEVEL', 'AMOUNT', 'WEIGHT'];
        $query = $this->newQuery()->select($select);
        if(!empty($out_id)){
            $query = $query->where('OUT_STORAGE_ID', '=', $out_id);
        }
        return $query->get();
    }

    //TOBACCO_YEAR_CODE:烟叶年份，INVENTORY_CODE：物料名称，GOODS_LEVEL：烟叶等级

}
