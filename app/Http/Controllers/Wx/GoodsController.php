<?php


namespace App\Http\Controllers\Wx;


use App\CodeResponse;
use App\Services\CatalogService;
use App\Services\GoodsService;
use Illuminate\Http\Request;

class GoodsController extends WxController
{
    protected $only = [];

    public function count()
    {
        $count = GoodsService::getInstance()->countGoodsOnSale();
        return $this->success($count);
    }

    public function category(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }

        $cur = CatalogService::getInstance()->getCategory($id);
        if (empty($cur)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }

        $parent = null;
        $children = null;
        if($cur->pid == 0){
            $parent = $cur;
            $children = CatalogService::getInstance()->getL2ListByPid($cur->id);
            $cur = $children->first() ?? $cur; // ??表示 全面为null时 使用后面对
        } else{
            $parent = CatalogService::getInstance()->getL1ById($cur->pid);
            $children = CatalogService::getInstance()->getL2ById($cur->pid);
        }

        return $this->success([
            'currentCategory'=>$cur,
            'parentCategory'=>$parent,
            'brotherCategory'=>$children
        ]);
    }
}
