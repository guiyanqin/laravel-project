<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Models;

use App\Models\ToBaseModel;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;

class VmDmGfgjInsectRecord extends ToBaseModel
{
    //use HasFactory;
    public function getData(){
        return $this->newQuery()->select(['GOODS_LOCATION_NAME'])
            ->orderBy('RECORD_TIME','DESC')
            ->get();
    }
}
