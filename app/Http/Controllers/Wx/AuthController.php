<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends WxController
{
    protected $only = ['info','profile'];

    public function info()
    {
        $user = $this->user();
        return $this->success([
            'nickName'=>$user->nickname,
            'avatar'=>$user->avatar,
            'gender'=>$user->gender,
            'mobile'=>$user->mobile
        ]);
    }

    public function profile(Request $request)
    {
        $user = $this->user();
        $avatar = $request->input('avatar');
        $gender = $request->input('gender');
        $nickname = $request->input('nickname');

        if(!empty($avatar)){
            $user->avatar = $avatar;
        }
        if(!empty($gender)){
            $user->gender = $gender;
        }
        if(!empty($nickname)){
            $user->nickname = $nickname;
        }
        $ret = $user->save();
        return $this->failOrSuccess($ret,CodeResponse::UPDATED_FAIL);
    }

    public function logout()
    {
        Auth::guard('wx')->logout();
        return $this->success();
    }

    public function reset(Request $request)
    {
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        if(empty($password) || empty($mobile) || empty($code)){
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        $isPass = UserService::getInstance()->checkCaptcha($mobile,$code);
        if(!$isPass){
            return $this->fail(CodeResponse::AUTH_CAPTCHA_UNMATCH);
        }

        $user = UserService::getInstance()->getByMobile($mobile);
        if(is_null($user)){
            return $this->fail(CodeResponse::AUTH_MOBILE_UNREGISTERED);
        }

        $user->password = Hash::make($password);
        $ret = $user->save();

        return $this->failOrSuccess($ret,CodeResponse::UPDATED_FAIL);

    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // 数据验证
        if (empty($username) && empty($password)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        // 验证账号是否存在
        $user = UserService::getInstance()->getByUsername($username);
        if (is_null($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }

        // 对密码进行校验
        $isPass = Hash::check($password, $user->getAuthPassword());
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT, '账号密码不对');
        }

        // 更新登陆对信息
        $user->last_login_time = now()->toDateString();
        $user->last_login_ip = $request->getClientIp();
        if (!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }

        // 获取token
        $token = Auth::guard('wx')->login($user);

        // 组装数据并返回
        return $this->success([
            'token' => $token,
            'userInfo' => [
                'nickname' => $username,
                'avatarUrl' => $user->avatar
            ]
        ]);
    }

    public function register(Request $request)
    {
        // todo 获取参数
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        // todo 验证参数是否为空
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        // todo 验证用户是否存在
        $user = UserService::getInstance()->getByUsername($username);
        //$user = $this->userService->getByUsername($username);
        //$user = (new UserService())->getByUsername($username);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED);
        }
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }
        $user = UserService::getInstance()->getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }
        // 验证验证码是否正确
        UserService::getInstance()->checkCaptcha($mobile, $code);

        // 写入数据表
        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = "https://thirdwx.qlogo.cn/mmopen/vi_32/ibFY0KibR500P0SjMKZsIN8rTy43AZ3qqTygmR1Lia0gjoLBnibXr3DPWHlF0yJMgNwH9sDAetkSkvC3VjIeTNjHdA/132";
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateString();
        $user->last_login_ip = $request->getClientIp();
        $user->save();

        // 新用户发券
        // token
        return $this->success([
            'token' => '',
            'userInfo' => [
                'nickname' => $username,
                'avatarUrl' => $user->avatar
            ]
        ]);
    }

    public function regCaptcha(Request $request)
    {
        // todo 获取手机号
        $mobile = $request->input('mobile');
        // todo 验证手机号是否合法
        if (empty($mobile)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        // todo 验证手机格式
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }

        // todo 验证手机号是否被注册
        $user = UserService::getInstance()->getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }

        // todo 防刷验证 , 一分钟内只能请求一次
        $lock = Cache::add('register_captcha_lock_' . $mobile, 1, 60);
        if (!$lock) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);

        }

        // todo 当天天只能请求 10 次
        $isPass = UserService::getInstance()->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY, '验证码当天发送不能超过10次');

        }
        // 生成验证码
        $code = UserService::getInstance()->setCaptcha($mobile);
        // 发送验证码
        UserService::getInstance()->sendCaptchaMsg($mobile, $code);
        return $this->success();
    }
}
