<?php

namespace App\Http\Controllers;

use App\Models\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    //
    public function getBookById($id)
    { // 根据id查询
        $book = (new Book())->findById($id);
        // return response()是返回想要 json()转换成json字符串但是是unicode setEncodingOptions设置编码
        return response()->json($book)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

}
