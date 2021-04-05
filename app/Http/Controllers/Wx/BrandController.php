<?php


namespace App\Http\Controllers\Wx;


use App\CodeResponse;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends WxController
{
    protected $only = [];

    public function detail(Request $request)
    {
        $id = $request->input('id',0);
        if(empty($id)){
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        $brand = BrandService::getInstance()->getBrand($id);
        if(is_null($brand)){
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        return $this->success($brand);
    }

    public function list(Request $request)
    {
        $page = $request->input('page',1);
        $limit = $request->input('limit',10);
        $sort = $request->input('sort','add_time');
        $order = $request->input('order','desc');

        $columns = ['id','name','desc','pic_url','floor_price'];
        $list = BrandService::getInstance()->getBrandList($page,$limit,$sort,$order,$columns);
        return $this->successPaginate($list);
    }
}
