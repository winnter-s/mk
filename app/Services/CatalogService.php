<?php


namespace App\Services;


use App\Models\Goods\Category;

class CatalogService extends BaseService
{
    public function getL1List()
    {
        return Category::query()->where('level', 'L1')
            ->where('deleted', 0)
            ->get();
    }

    public function getL2ListByPid(int $pid)
    {
        return Category::query()->where('level', 'L2')
            ->where('pid', $pid)
            ->where('deleted', 0)
            ->get();
    }

    public function getL1ById(int $id)
    {
        return Category::query()->where('level', 'L1')
            ->where('id', $id)
            ->where('deleted', 0)
            ->first();
    }

    public function getCategory(int $id)
    {
        return Category::query()->find($id);
    }
}
