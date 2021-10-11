<?php


namespace App\Modules\DigitalWin\EnShi\Models;


use App\Models\BaseModel;

class getPartition extends BaseModel
{
    public function getPartition(){
        $select = [];
        return $this->newQuery()->from()
            ->get();
    }


}
