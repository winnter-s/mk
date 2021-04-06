<?php


namespace App\Http\Controllers\Wx;


use App\CodeResponse;
use App\Constant;
use App\Services\CatalogService;
use App\Services\GoodsService;
use App\Services\SearchHistoryService;
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
        if ($cur->pid == 0) {
            $parent = $cur;
            $children = CatalogService::getInstance()->getL2ListByPid($cur->id);
            $cur = $children->first() ?? $cur; // ??表示 全面为null时 使用后面对
        } else {
            $parent = CatalogService::getInstance()->getL1ById($cur->pid);
            $children = CatalogService::getInstance()->getL2ById($cur->pid);
        }

        return $this->success([
            'currentCategory' => $cur,
            'parentCategory' => $parent,
            'brotherCategory' => $children
        ]);
    }

    public function list(Request $request)
    {
        $categoryId = $request->input('categoryId');
        $brandId = $request->input('brandId');
        $keyword = $request->input('keyword');
        $isNew = $request->input('isNew');
        $isHot = $request->input('isHot');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');

        if ($this->isLogin() && !empty($keyword)) {
            SearchHistoryService::getInstance()->save($this->userId(), $keyword, Constant::SEARCH_HISTORY_FROM_WX);
        }

        // 优化参数传递
        $columns = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price','add_time','update_time'];
        $goodsList = GoodsService::getInstance()->listGoods($categoryId, $brandId, $isNew, $isHot, $keyword, $columns, $sort, $order, $page, $limit);

        $categoryList = GoodsService::getInstance()->listL2Category($brandId, $isNew, $isHot, $keyword);

        $goodsList = $this->paginate($goodsList);
        $goodsList['filterCategoryList'] = $categoryList;
        return $this->success($goodsList);
    }
}
