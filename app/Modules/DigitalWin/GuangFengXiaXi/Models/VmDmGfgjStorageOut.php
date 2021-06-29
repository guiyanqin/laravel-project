<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Models;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ToBaseModel;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;

class VmDmGfgjStorageOut extends ToBaseModel
{

    //FDESC:收发类别名称,SEND_RECEIVE_TYPE:收发类别
    public function getOptions($arr = ['name'=>'SEND_RECEIVE_TYPE', 'value'=>'FDESC'])
    {
        return $this->defaultQuery()
            ->select([$arr['name']. ' as name', $arr['value']. ' as value'])//查询名字
            ->groupBy([$arr['name'], $arr['value']])//按名字和值进行分组
            ->orderBy($arr['name'], 'ASC')//按照名字升序排序
            ->get();
    }
    //查询功能
    public function search($where = [])
    {
        $select = [
            'a.ID', 'a.BILL_NO', 'a.REALNAME', 'a.BILL_DATE', 'a.OUT_REPOSITORY_NAME', 'a.BATCH_NO',
            'a.FDESC', 'a.STATUS', 'b.TOBACCO_GRADE', 'b.WEIGHT'
        ];
        $query = $this->newQuery()->from('VM_DM_GFGJ_STORAGE_OUT as a')
            ->leftJoin('VM_DM_GFGJ_STORAGE_OUT_ITEM as b', 'b.OUT_STORAGE_ID', '=', 'a.ID')
            ->select($select);
        //获取单据编号
        if(!empty($where['code'])){
            $query = $query->where('BILL_NO', 'like', '%'.$where['code'].'%');
        }
        //烟丝牌号
        if(!empty($where['grade'])){
            $query = $query->where('b.TOBACCO_GRADE', 'like', '%'.$where['grade'].'%');
        }
        // 单据状态:BILL_STATUS
        if(!empty($where['bill_status'])){
            $query = $query->where('BILL_STATUS', '=', $where['bill_status']);
        }
        // 收货人
        if(!empty($where['name'])){
            $query = $query->where('a.REALNAME','=',$where['name']);
        }

        //所在栋数ID
        if(!empty($where['build'])){
            $query = $query->where('STORE_HOUSE_ID', '=', $where['build']);
        }
        //STORE_HOUSE_NAME：栋名称
        if(!empty($where['build_name'])){
            $query = $query->where('STORE_HOUSE_NAME', '=', $where['build_name']);
        }
        //收货时间
        if (!empty($where['start_date'])){
            if(empty($where['end_date'])){
                $query = $query->where('BILL_DATE','>',$where['start_date']);
                $query = $query->where('BI_DATE', [$where['start_date'],$where['end_date']]);
            }

        }
        //AUDIT_STATUS:审核状态
        //bill_status:单据状态


        return $query->orderBy('BILL_DATE', 'DESC')->get();

    }

    public function outLog($len = 3, $status = false)
    {
        $select = ['a.BILL_NO', 'a.BILL_DATE', 'b.AMOUNT', 'b.WEIGHT', 'b.GOODS_LOCATION_NAME'];
        $query = $this->newQuery()->from('VM_DM_GFGJ_STORAGE_OUT as a')
            ->leftJoin('VM_DM_GFGJ_STORAGE_OUT_ITEM as b', 'b.OUT_STORAGE_ID', '=', 'a.ID')
            ->select($select)
            ->where('a.BILL_DATE', '<', date("Y-m-d", strtotime("-".$len." days")));
        if($status){
            $query = $query->whereIn('a.OUT_STORAGE', [753,752,737]);
        }
        return $query->get();
    }

    private function defaultQuery(): Builder
    {
        return $this->newQuery();
    }
}
