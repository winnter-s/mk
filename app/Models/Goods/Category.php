<?php


namespace App\Models\Goods;


use App\Models\BaseModel;

class Category extends BaseModel
{
    protected $table = 'category';

    protected $casts = [
        'deleted' => 'boolean',
    ];

}
