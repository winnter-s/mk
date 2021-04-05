<?php


namespace App\Services;


use App\CodeResponse;
use App\Models\User\Address;

class AddressService extends BaseService
{
    public function getAddressListByUserId(int $userId)
    {
        return Address::query()->where('user_id', $userId)
            ->where('deleted', 0)
            ->get();
    }

    public function getAddress($userId, $addressId)
    {
        return Address::query()->where('user_id', $userId)
            ->where('id', $addressId)
            ->where('deleted', 0)
            ->first();
    }

    public function delete($userId,$addressId)
    {
        $address = $this->getAddress($userId,$addressId);
        if(is_null($address)){
            $this->throwBusinessException(CodeResponse::PARAM_ILLEGAL);
        }
        return $address->delete();
    }
}
