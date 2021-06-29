<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

final class Func{

    /**
     * 获取当前域名
     * @return string
     */
    public static function getHost(){
        $scheme = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $url = $scheme.$_SERVER['HTTP_HOST'];

        return $url;
    }

    /**
     * 文件转移
     * @param $path
     * @return string|string[]|boolean
     */
    public static function fileMove($path){
        $newPath = str_replace('/tmp','',$path);
        $exits = Storage::disk('public')->exists($path);
        if(!$exits && !Storage::disk('public')->exists($newPath)){
            return false;
        }
        if($exits){   //上传文件存在进行转移存储地址
            Storage::disk('public')->move($path, $newPath);
        }
        return $newPath;
    }

    /**
     * 文件删除
     * @param $path
     * @return bool
     */
    public static function fileDelete($path = '')
    {
        if(!empty($path) && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    /**
     * json文件地址批量转换
     * @param String $str   json字符串
     * @return array|mixed
     */
    public static function fileUrlJsonToArr($str)
    {
        $arr = [];
        if(!empty($str)){
            $arr = json_decode($str, true);
            if(!empty($arr)){
                foreach($arr as $k => $v){
                    $arr[$k] = self::getFileUrl($v);
                }
            }else{
                $arr[] = self::getFileUrl($str);
            }
        }

        return $arr;
    }

    /**
     * 获取存储目录地址
     * @return string
     */
    public static function getStorageHost()
    {
        return self::getHost().'/storage';
    }

    /**
     * 设置文件完整请求地址
     * @param $url
     * @return string
     */
    public static function getFileUrl($url)
    {
        return self::getStorageHost().$url;
    }

    /**
     * 重设文件地址
     * @param $url
     * @return string|string[]
     */
    public static function resetFileUrl($url)
    {
        return str_replace(self::getStorageHost(), '', $url);
    }

    /**
     * 返回操作成功
     * @param string $message
     * @param array $data
     * @param int $code
     */
    public static function ajaxSuccess($message = '', $data = [], $code = 200)
    {
        self::jsonEncode($message, $data, $code);
    }

    /**
     * 返回操作失败
     * @param string $message
     * @param array $data
     * @param int $code
     */
    public static function ajaxError($message = '', $data = [], $code = -1)
    {
        self::jsonEncode($message, $data, $code);
    }

    /**
     * 输出json数据格式
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    protected static function jsonEncode($message = '', $data, $code)
    {
        $_type = "application/json";

        if( isset($_SERVER["HTTP_USER_AGENT"]) && preg_match("/msie\s*\d/i", $_SERVER["HTTP_USER_AGENT"]) ) {
            $_type = "text/plain";
        }

        header('Content-type:'.$_type.'; charset=utf-8');

        $msg = '操作失败';
        if($code == 200) {
            $msg = '操作成功';
        } elseif($code == 401) {
            $msg = '未授权登陆认证';
        }
        $message = $message ?: $msg;
        $token = [];
        $page = [];
        if(isset($data['token'])) {
            $token = $data['token'];
            unset($data['token']);
        }
        if(isset($data['page'])) {
            $page = $data['page'];
            unset($data['page']);
        }
        $retdata = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        if($token) {
            $retdata['token'] = $token;
        }
        if($page) {
            $retdata['page'] = $page;
        }

        //$code = $code == -1 ? 406 : $code;
        if(in_array($code, [400, 401, 402, 403, 404, 405, 406, 407, 408, 500, 501, 502, 503, 504, 505])) {
            http_response_code($code);
        }
//        echo json_encode($retdata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        echo json_encode($retdata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit();
    }

    /**
     * 计算方差、标准差、平均值
     * @param $arr
     * @return array|int[]
     */
    public static function variance($arr)
    {
        $length = count($arr);
        if ($length == 0) {
            return array(0,0);
        }
        $average = array_sum($arr) / $length;
        $count = 0;
        foreach ($arr as $v) {
            $count += pow($average-$v, 2);
        }
        $variance = $count / $length;
        return array('variance' => $variance, 'square' => sqrt($variance), 'average' => $average);
    }

    /**
     * 获取开始时间和结束时间，返回格式：Y-m-d H:i:s
     * @param int $dateLength
     * @return array
     */
    public static function startToEndDate($dateLength = 30)
    {
        $startDate = isset($_REQUEST['start_date']) ? trim($_REQUEST['start_date']) : '';
        $endDate = isset($_REQUEST['end_date']) ? trim($_REQUEST['end_date']) : '';
        $startStamp = strtotime($startDate);
        $endStamp = strtotime($endDate);
        if($startStamp && $endStamp) {
            if(($endStamp - $startStamp) > $dateLength * 24 * 3600) {
                self::ajaxError("查询时间长度不能超过{$dateLength}天");
            }
        } else {
            //当天开始和结束时间
            $startDate = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
            $endDate = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
        }
        return ['start_date' => $startDate, 'end_date' => $endDate];
    }
}
