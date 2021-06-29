<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

class UserController extends Controller
{
    //
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
    //根据id查询图书类型
    public function getType($id)
    {
        $user = Book_type::find($id);
        return response()->json($user);
    }

}
