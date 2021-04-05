<?php


namespace App\Http\Controllers\Wx;


use App\CodeResponse;
use App\Models\User\Address;
use App\Services\AddressService;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends WxController
{
    protected $only = [];

    public function index(Request $request)
    {
        $id = $request->input('id', 0);
        $l1List = CatalogService::getInstance()->getL1List();
        if (empty($id)) {
            $current = $l1List->first();
        } else {
            $l1List->where('id', $id)->first();
        }

        $l2List = null;
        if (!is_null($current)) {
            $l2List = CatalogService::getInstance()->getL2ListByPid($current->id);
        }

        return $this->success([
            'categoryList' => $l1List,
            'currentCategory' => $current,
            'currentSubCategory' => $l2List
        ]);

    }

    public function current(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        $category = CatalogService::getInstance()->getL1ById($id);
        if (is_null($category)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }

        $l2List = CatalogService::getInstance()->getL2ListByPid($category->id);
        return $this->success([
            'currentCategory' => $category,
            'currentSubCategory' => $l2List->toArray()
        ]);
    }
}
