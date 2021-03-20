<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // todo 获取参数
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        // todo 验证参数是否为空
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return ['errno' => 401, 'errmsg' => '参数不对'];
        }
        // todo 验证用户是否存在
        $user = (new UserService())->getByUsername($username);
        if (!is_null($user)) {
            return ['errno' => 704, 'errmsg' => '用户名已注册'];
        }
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return ['errno' => 707, 'errmsg' => '手机号格式不正确'];
        }
        $user = (new UserService())->getByMobile($mobile);
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已注册'];
        }

        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = "https://thirdwx.qlogo.cn/mmopen/vi_32/ibFY0KibR500P0SjMKZsIN8rTy43AZ3qqTygmR1Lia0gjoLBnibXr3DPWHlF0yJMgNwH9sDAetkSkvC3VjIeTNjHdA/132";
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateString();
        $user->last_login_ip = $request->getClientIp();
        $user->save();

        return [
            'errno' => 0, 'errmsg' => '成功', 'data' => [
                'token' => '',
                'userinfo' => [
                    'nickname' => $username,
                    'avatarUrl' => $user->avatar
                ]
            ]
        ];
    }
}
