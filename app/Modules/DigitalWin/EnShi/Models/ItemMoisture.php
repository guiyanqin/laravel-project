<?php

namespace App\Modules\DigitalWin\EnShi\Models;

use App\Models\BaseModel;

class ItemMoisture extends BaseModel
{
    //查找烟叶类型，等级，年份
    public function findFrom($data = [], $size = 10)
    {
        $select = ['a.id', 'a.batch', 'a.ratio', 'a.user', 'a.shape_id', 'b.name as shape',
            'a.year_id', 'c.name as year', 'a.level_id', 'd.name as level', 'test_date'
        ];
        $query = $this->newQuery()->from('item_moisture as a')
            ->leftJoin('options as b', 'b.id', '=', 'a.shape_id')
            ->leftJoin('options as c', 'c.id', '=', 'a.year_id')
            ->leftJoin('options as d', 'd.id', '=', 'a.level_id')
            ->select($select)
        ;
        if(!empty($data['keyword'])){
            $keyword = trim($data['keyword']);
            //trim（）函数移除字符串两侧的空白字符或其他预定义字符
            $where = ['batch', 'like', '%'.$keyword.'%'];
            $query = $query->where($where);
        }
        if(!empty($data['shape_id'])){
            $query = $query->where('shape_id', '=', $data['shape_id']);
        }
        if(!empty($data['year_id'])){
            $query = $query->where('year_id', '=', $data['year_id']);
        }
        if(!empty($data['level_id'])){
            $query = $query->where('level_id', '=', $data['level_id']);
        }
        return $query->paginate($size);
    }

    //烟叶ID
    public function findById($id)
    {
        if(empty($id)) return false;
        return $this->newQuery()->find($id);
    }

    public function findByBatch($batch, $id = '')
    {
        $query = $this->newQuery()->where(['batch'=>$batch]);
        if(!empty($id)){
            $query->where('id', '!=', $id);
        }
        return $query->first();
    }

}
