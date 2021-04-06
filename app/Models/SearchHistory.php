<?php


namespace App\Models\Goods;


use App\Models\BaseModel;

class SearchHistory extends BaseModel
{
    protected $table = 'Search_history';


    protected $fillable = [
        'user_id',
        'keyword',
        'from'
    ];
    protected $casts = [
        'deleted' => 'boolean',
    ];

}
