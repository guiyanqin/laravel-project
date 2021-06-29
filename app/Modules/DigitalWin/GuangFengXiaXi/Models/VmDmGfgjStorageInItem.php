<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Models;

use App\Models\ToBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VmDmGfgjStorageInItem extends ToBaseModel
{
    use HasFactory;
    //计算总重
    public function getTotal($key = 'weight',$where = []): int
    {
        $query = $this->newQuery();
        if(!empty($where)){
            $query = $query->where($where);
        }
        return $query->sum($key);

    }
    //获取入库详情信息
    public function getData($Id)
    {
        $select = ['NAME', 'YEAR', 'GOODS_LEVEL', 'AMOUNT', 'WEIGHT'];
        $query = $this->newQuery()->select($select);
        if(!empty($in_id)){
            $query = $query->where('IN_ID', '=', $in_id);
        }
        return $query->get();
    }
}
