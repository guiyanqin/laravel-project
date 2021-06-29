<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class OracleModel extends BaseModel
{
    //对应database连接名
    //protected $connection = 'oracle1';

    //任务执行平均时间
    public $taskAverageTime = 45;  //分钟

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        //
        $connection = "oracle1";
        //$connection = "mysql";
        $this->setConnection($connection);
    }

    public function setConnection($name)
    {
        return parent::setConnection($name);
    }
}
