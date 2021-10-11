<?php

namespace App\Modules\DigitalWin\EnShi\Models;

use App\Models\ToBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

//use Illuminate\Database\Eloquent\Model;

class VmDmGfgjStorageIn extends ToBaseModel
{
    use HasFactory;
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
        $select = ['ID','BILL_NO','RECEIPT_DATE', 'REALNAME', 'STORE_REGION_NAME', 'STORE_HOUSE_NAME',
            'FDESC', 'BILL_STATUS', 'QC_STATUS', 'AUDIT_STATUS'];
        $query = $this->defaultQuery()->select($select);
        //var_dump($query->get());exit;
        //获取单据编号
        if(!empty($where['code'])){
            $query = $query->where('BILL_NO', 'like', '%'.$where['code'].'%');
        }
        // 单据状态:BILL_STATUS
        if(!empty($where['bill_status'])){
            $query = $query->where('BILL_STATUS', '=', $where['bill_status']);
        }
        // 收货人
        if(!empty($where['name'])){
            $query = $query->where('REALNAME','=',$where['name']);
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
                $query = $query->where('RECEIPT_DATE','<',$where['start_date']);
                //接收时间大于当前日期的数据显示出来
                //$query = $query->where('RECEIPT_DATE', [$where['start_date'],$where['end_date']]);
            }
        }
        //AUDIT_STATUS:审核状态
        if(!empty($where['audit_status'])) {
            $query = $query->where('AUDIT_STATUS', '=', $where['audit_status']);
        }

        return $query->orderBy('RECEIPT_DATE', 'DESC')->get();

    }

    /**
     * @param int $len
     * @param bool $status
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */

    public function inLog($len = 3, $status = false)
    {
        $select = ['a.BILL_NO', 'a.RECEIPT_DATE', 'b.AMOUNT', 'b.WEIGHT', 'b.GOODS_LOCATION_NAME'];
        $query = $this->newQuery()->from('VM_DM_GFGJ_STORAGE_IN as a')
            //当入库详情ID等于入库ID，则返回入库详情表信息
            ->leftJoin('VM_DM_GFGJ_STORAGE_IN_ITEM as b', 'b.IN_ID', '=', 'a.ID')
            ->select($select)
            ->where('a.RECEIPT_DATE', '<', date("Y-m-d", strtotime("-".$len." days")));
        if($status){
            $query = $query->whereIn('a.STORE_HOUSE_ID', [753,752,737]);
        }
        return $query->get();
    }

    private function defaultQuery()
    {
        return $this->newQuery();
    }
}
