<?php

namespace App\Http\Controllers;

use App\Helpers\Exceptions\ControllerException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 分页列表数据格式化
     * @param LengthAwarePaginator $data
     * @return array
     */
    public function getPageList(LengthAwarePaginator $data): array
    {
        return [
            'list' => $data->items(),
            'page' => [
                'page_index' => $data->currentPage(),
                'page_size' => $data->perPage(),
                'page_count' => $data->lastPage(),
                'count' => $data->total()
            ]
        ];
    }

    /**
     * 数据通用验证
     * @param $data
     * @param $rule
     * @param $messages
     * @return bool
     * @throws ControllerException
     */
    public function verification($data,$rule,$messages): bool
    {
        $validator = Validator::make($data,$rule,$messages);
        if($validator->fails()){
            throw new ControllerException($validator->getMessageBag()->first());
        }
        //返回结果失败，抛出异常

        return true;
    }
}
