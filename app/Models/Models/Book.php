<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    public $table = 'book_type';
    use HasFactory;

    public function findById($id)
    {
        return $this->newQuery()
            ->where('id', '=', $id)
            ->get();
    }
}
