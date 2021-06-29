<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model
{
    public $pageNo = 1;
    public $pageSize = 10;
    public $offset = 0;
    public $limit = 0;
    public $totalCount = 0;

    /**
     * 默认使用时间戳戳功能
     *
     * @var bool
     */
    //public $timestamps = true;

    public $prefix = '';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        //sql调试
        \DB::connection()->enableQueryLog();
        //分页信息
        $pageNo = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
        if(!$pageNo) {
            //page_index，页面首页
            $pageNo = isset($_REQUEST['page_index']) ? (int)$_REQUEST['page_index'] : 0;
        }
        $pageSize = isset($_REQUEST['size']) ? (int)$_REQUEST['size'] : 0;
        if(!$pageSize) {
            //页面大小
            $pageSize = isset($_REQUEST['page_size']) ? (int)$_REQUEST['page_size'] : 0;
        }

        $this->pageNo = $pageNo ?: $this->pageNo;
        $this->pageSize = $pageSize ?: $this->pageSize;
        //
        $this->offset = $this->pageSize * ($this->pageNo - 1);
        $this->limit = $this->pageSize;
        //表前缀
        $this->prefix = DB::connection($this->connection)->getConfig('prefix');
    }

    /**
     * 避免转换时间戳为时间字符串
     *
     * @param DateTime|int $value
     * @return DateTime|int
     */
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }

    /**
     * 返回表名，不用处理单数复数
     *
     */
    public function getTable()
    {
        return $this->table ?? Str::snake(class_basename($this));
    }

    /**
     * 返回当前的数据库类型表连接
     * @return \Illuminate\Database\Query\Builder
     */
    public function dbTable()
    {
        return DB::connection($this->connection)->table($this->getTable());
    }

    /**
     * 返回当前数据库类型表前缀
     * @return mixed
     */
    public function dbTablePrefix()
    {
        return DB::connection($this->connection)->getTablePrefix();
    }

    public function fieldData($data, $fields = '')
    {
        //$tableFields      = array_keys($this->attributes);
        $tableFields = Schema::getColumnListing($this->getTable());
        $createTimeFields = ['created_at', 'create_time', 'add_time'];
        $updateTimeFields = ['updated_at', 'update_time'];
        $now = time();
        if($fields) {
            $fieldArr = is_array($fields) ? $fields : array_filter(explode(',', $fields));
        }
        if(isset($data['id'])) {
            unset($data['id']);
        }
        if(isset($data['created_at'])) {
            unset($data['created_at']);
        }
        foreach($data as $k => $val) {
            if(is_array($val) || !in_array($k, $tableFields)) {
                continue;
            }
            if(empty($fieldArr)) {
                $this->$k = $val;
            } elseif(in_array($k, $fieldArr)) {
                $this->$k = $val;
            }
        }
        if(!$this->original) {  // 添加
            isset($this->id) && $this->id = null;  // 添加时，主键为null
            foreach ($createTimeFields as $field) {
                if(in_array($field, $tableFields)) {
                    $this->$field = $now;
                }
            }
        }
        foreach ($updateTimeFields as $field) {  // 添加或更新
            if(in_array($field, $tableFields)) {
                $this->$field = $now;
            }
        }
    }

    public function getLists($where = [], $fields = '', $order = [])
    {
        $model = $this;
        //条件
        if($where) {
            if(is_array($where)) {
                $or = [];
                $and = [];
                $like = [];
                if(isset($where['OR'])) {
                    $or = $where['OR'];
                    unset($where['OR']);
                }
                if(isset($where['AND'])) {
                    $and = $where['AND'];
                    unset($where['AND']);
                }
                if(isset($where['LIKE'])) {
                    $like = $where['LIKE'];
                    unset($where['LIKE']);
                }
                if($where) {
                    $model = $model->where($where);
                }
                if($and) {
                    $model = $model->where($and);
                }
                if($or) {
                    $model = $model->orWhere($or);
                }
                if($like) {
                    foreach($like as $fk => $lkval) {
                        $model = $model->where($fk, 'like', '%'.$lkval.'%');
                    }
                }
            } else {
                $model = $model->whereRaw($where);
            }
        }
        //总记录数
        $this->totalCount = $model->count();
        //排序
        if($order && is_array($order)) {
            foreach($order as $k => $v) {
                $model = $model->orderBy($k, $v);
            }
        }
        //字段
        $fieldsArr = array_filter(explode(',', $fields));
        if($fieldsArr) {
            $model = $model->select($fieldsArr);
        }
        return $this->setPage($model->offset($this->offset)->limit($this->limit)->get());
    }

    public function setPage($data)
    {
        return [
            'list' => $data,
            'page' => [
                'page_index' => $this->pageNo,
                'page_size' => $this->pageSize,
                'page_count' => ceil($this->totalCount / $this->pageSize),
                'count' => $this->totalCount,
            ],
            //'log' => \DB::getQueryLog(),
        ];
    }

    /**
     * 批量插入
     * @param array $data
     * @return bool
     */
    public function insertAll(Array $data)
    {
        $rs = DB::table($this->getTable())->insert($data);
        return $rs;
    }

    /**
     * 获取数据表中某一列所有值
     *
     */
    public function getAll($field)
    {
        $result = self::select($field)->get()->toArray();
        return $result;
    }

    /**
     * 生成存储过程调用函数
     * @param string $produre_name 存储过程名称
     * @param array  $params       调用的参数数组
     * @return array ['status' => , 'data' => ]
     */
    public function execProdure($produre_name, $params = [])
    {
        $out_status = 'out_status';   // 存储过程的状态记录
        if (!empty($params)) {
            //$sql = "CALL {$produre_name}('" . implode("','", $params) . "', @{$out_status});";
            $sql = "CALL {$produre_name}('" . implode("','", $params) . "')";
        } else {
            //$sql = "CALL {$produre_name}(@{$out_status});";
            $sql = "CALL p_shoufacuninventory(@result, '', '', '', '', '', '')";
        }
        $produre_log = storage_path() . '/procedure/' . date('Ymd') . '.log';
        $log_msg = "\n\nsql: {$sql}\n\n";
        $return_data = ['sql' => $sql];
        //DB::connection($this->connection)->statement("SET @{$out_status} = 0");
        try {
            $data = DB::connection($this->connection)->select($sql);
            //$status = DB::connection($this->connection)->select('SELECT @' . $out_status);
            $log_msg .= "data: " . json_encode($data);
            //$return_data['status'] = $status[0]->{'@'.$out_status};
            $return_data['data'] = $data;
        } catch(Exception $e) {
            $log_msg .= 'error: ' . $e->getMessage();
            $return_data['status'] = 1;
            $return_data['data'] = [];
        }
        // 存储过程写入日志
        //@file_put_contents($produre_log, $log_msg, FILE_APPEND);
        return $return_data;
    }
}
