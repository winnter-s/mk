<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'user';
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $hidden = [
        'password',
        'deleted'
    ];

    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    // 返回自定义信息到jwt message中
    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [
            'iss' => env('JWT_ISSURE'),
            'userId' => $this->getKey()
        ];
    }
}
