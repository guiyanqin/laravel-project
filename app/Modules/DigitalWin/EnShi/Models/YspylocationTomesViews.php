<?php


namespace App\Modules\DigitalWin\EnShi\Models;


use App\Models\ToBaseModel;

class YspylocationTomesViews extends ToBaseModel
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection('mysql');
    }
    public function getData()
    {
        return $this->newQuery()->get();
    }
}
