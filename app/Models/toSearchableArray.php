<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class toSearchableArray extends Model
{
    use HasFactory;
    public function toSearchableArray()
    {
        $array = $this->toArray();

        return array('id' => $array['id'], 'name' => $array['name']);

    }
}
