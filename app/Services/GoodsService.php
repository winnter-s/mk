<?php


namespace App\Services;


use App\Models\Goods\Brand;
use App\Models\Goods\Goods;
use PhpParser\Builder;

class GoodsService extends BaseService
{
    public function countGoodsOnSale()
    {
        return Goods::query()->where('is_on_sale',1)
            ->where('deleted',0)->count('id');
    }
}
