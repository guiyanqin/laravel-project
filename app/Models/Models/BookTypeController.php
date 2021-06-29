<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookTypeController extends Model
{
    use HasFactory;
    //根据id查询图书类型

    /**
     * 关联到模型的数据表
     * @var string
     */
    protected $table = 'book_type';
    /**
     * Laravel有默认时间字段，如果不需要则去除
     * 表明模型是否应该被打上时间戳
     * @var bool
     */
    public $timestamps = false;

    public function getType($id)
    {

        $user = BookTypeController::find($id);
        return response()->json($user);
    }

}
