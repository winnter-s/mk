<?php


namespace App\Http\Controllers\Wx;


use App\CodeResponse;
use App\Models\User\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddressController extends WxController
{
    public function list()
    {
        $list = AddressService::getInstance()->getAddressListByUserId($this->user()->id);
        return $this->successPaginate($list);

    }

    public function detail()
    {

    }

    public function save()
    {

    }

    public function delete(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id) && !is_numeric($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        AddressService::getInstance()->delete($this->user()->id, $id);
        return $this->success();

    }
}
