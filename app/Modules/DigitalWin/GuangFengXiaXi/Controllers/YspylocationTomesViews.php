<?php


namespace App\Modules\DigitalWin\GuangFengXiaXi\Controllers;


use App\Models\ToBaseModel;

class YspylocationTomesViews extends ToBaseModel
{

    /**
     * YspylocationTomesViews constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection('mysql');
    }

    public function getData()
    {
        return $this->newQuery()->get();
    }
    //通过“new”实例化对象、并输出类的方法和功能
}
