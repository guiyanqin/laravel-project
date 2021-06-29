<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Models;

use App\Models\BaseModel;

class Setting extends BaseModel
{
    protected $fillable = ['option', 'value', 'user'];

    public function findByOption($key)
    {
        return $this->newQuery()->where('option', '=', $key)->first();
    }

    public function create($key, $data = []): bool
    {
        $model = $this->findByOption($key);
        if(empty($model)){
            $model = $this;
            $model->setAttribute('option', $key);
            //setAttribute() 方法可以用来设置数据库句柄的属性
        }
        foreach($data as $key => $val){
            $model->setAttribute($key, $val);
        }
        return $model->save();
    }
}
