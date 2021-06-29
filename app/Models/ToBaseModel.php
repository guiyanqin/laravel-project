<?php

namespace App\Models;

class ToBaseModel extends BaseModel
{
    //连接到mysql数据库
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection("mysql");
    }

}
