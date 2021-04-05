<?php


namespace App\Models\User;


use App\Models\BaseModel;

class Address extends BaseModel
{
    protected $table = 'address';

    protected $casts = [
        'deleted' => 'boolean',
        'is_default' => 'boolean'
    ];
}
