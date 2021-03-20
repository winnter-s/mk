<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerificationCode;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

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

    public function regCaptcha(Request $request)
    {
        // todo 获取手机号
        $mobile = $request->input('mobile');

        // todo 验证手机号是否合法
        if (empty($mobile)) {
            return ['errno' => 401, 'errmsg' => '参数不对'];
        }

        // todo 验证手机格式
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return ['errno' => 707, 'errmsg' => '手机号格式不正确'];
        }

        // todo 验证手机号是否被注册
        $user = (new UserService())->getByMobile($mobile);
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已注册'];
        }

        // todo 随机生成 6 为验证码
        $code = random_int(100000,999999);
        // todo 防刷验证 , 一分钟内只能请求一次 ， 当天天只能请求 10 次

        $lock = Cache::add('register_captcha_lock_'.$mobile , 1 ,60);
        if(!$lock){
            return ['errno' => 702, 'errmsg' => '验证码未超时1分钟,不能发送'];
        }
        // todo 当天天只能请求 10 次
        $countKey = 'register_captcha_count_'.$mobile;
        if(Cache::has($countKey)){
            $count = Cache::increment($countKey);
            if($count>10){
                return ['errno'=>702,'errmsg'=>'验证码当天发送不能超过10次'];
            }
        } else {
            Cache::put($countKey,1,Carbon::tomorrow()->diffInSeconds(now()));
        }

        // todo 保存手机号和验证码的关系
        Cache::put('register_captcha'.$mobile, $code ,600);

        // todo 发送短信
        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber(18802988922,86)
        )->notify(new VerificationCode($code));
        return ['errno'=>0,'errmsg'=>'成功','data'=>null];
    }
}
