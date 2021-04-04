<?php


namespace App\Http\Controllers\Wx;


use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Support\Str;

class AddressController extends WxController
{
    public function list()
    {
        $list = AddressService::getInstance()->getAddressListByUserId($this->user()->id);
        $list->map(function (Address $address){
            $address = $address->toArray();
            $item = [];
            foreach ( $address as $key => $value){
                $key = lcfirst(Str::studly($key));
                $item[$key] = $value;
            }
            return $item;
        });

        return $this->success([
            'total'=>$list->count(),
            'page'=>1,
            'list'=>$list->toArray(),
            'pages'=>1,
            'limit'=>$list->count()
        ]);
    }

    public function detail()
    {

    }

    public function save()
    {

    }

    public function detele()
    {

    }
}
