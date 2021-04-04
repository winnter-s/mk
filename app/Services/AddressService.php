<?php


namespace App\Services;


use App\Models\Address;

class AddressService extends BaseService
{
    public function getAddressListByUserId(int $userId)
    {
        return Address::query()->where('user_id',$userId)
            ->where('deleted',0)
            ->get();
    }
}
