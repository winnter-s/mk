<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';

    protected $casts = [
        'deleted' => 'boolean',
        'is_default' => 'boolean'
    ];
}
