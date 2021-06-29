<?php

namespace App\Modules\DigitalWin\GuangFengXiaXi\Models;
use \Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\BaseModel;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Options extends BaseModel
{
    public function findFrom($keyword = '', $type = 0, $size = 10): LengthAwarePaginator
    {
        $query = $this->newQuery();
        if(!empty($keyword)){
            $keyword = trim($keyword);
            //trim()移除字符串两侧的字符
            $query = $query->where('name', 'like', '%'.$keyword.'%');
        }
        if(!empty($type)){
            $query = $query->where('type', '=', $type);
        }
        return $query->paginate($size);
    }


    public function findById($id)
    {
        if(empty($id)) return false;
        return $this->newQuery()->find($id);
    }

    /**
     * 录入信息
     * @param $data
     * @return bool
     */
    public function create($data): bool
    {
        if(!empty($data['id'])){
            $model = $this->findById($data['id']);
        }else{
            $model = $this;
        }
       /*
        1 setAttribute() 方法可以用来设置数据库句柄的属性
        PDO::setAttribute(int $attribute, mixed $value)
       第一个参数 $attribute 提供 PDO 对象特定的属性名
       第二个参数 $value 则是为这个指定的属性赋一个值
        2 getAttribute() 方法只需要提供一个参数，
        传递一个特定的属性名称，执行成功后会返回该属性所指定的值，否则返回 NULL
       语法格式如下所示：
        PDO::getAttribute(int $attribute)*/
        $model->setAttribute('name', $data['name']);
        $model->setAttribute('user', $data['user']);
        $model->setAttribute('type', $data['type']);

        return $model->save();
        //要从模型新增或修改一条数据到数据库，只要建立一个模型实例并调用 save 方法即可
    }
}
