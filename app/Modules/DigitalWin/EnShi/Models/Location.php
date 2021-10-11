<?php

namespace App\Modules\DigitalWin\EnShi\Models;

use App\Models\ToBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends ToBaseModel
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        ////调用或者说继承父类的构造函数:
        $this->setConnection('oracle2');
    }

    public function getUseWeight(): int
    {
        //从LOCATION表中返回要返回的值
        return $this->newQuery()->from('LOCATION as a')
            ->leftJoin('CONTAINERDETAIL as b', 'b.CONTAINERID', '=', 'a.CONTAINERID')
            ->leftJoin('ITEM as c', 'c.ITEMID', '=', 'b.ITEMID')
            ->where('a.LOCATIONSTATE', '=', 'LocationState_Stored')
            ->where('a.CONTAINERBARCODE', '<>', '99999999')
            ->where('a.ZONEID', '=', 'd2ae0ad36b0d42beb558499ffd90d7eb')
            ->sum('c.WEIGHT');
    }
}
