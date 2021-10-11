<?php

namespace App\Modules\DigitalWin\EnShi\Models;

use App\Models\BaseModel;
//查找烟叶类型，等级，年份的日志文件
class ItemMoistureLog extends BaseModel
{
    public $timestamps = false;
    public function findByBatch($keyword = '', $size = 10)
    {
        return $this->newQuery()->where(['batch'=>$keyword])->orderBy('test_date', 'desc')->paginate($size);
    }

}
