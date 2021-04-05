<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;
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
