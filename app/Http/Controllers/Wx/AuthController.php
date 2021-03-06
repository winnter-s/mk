<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Models\User\User;
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

        // ????????????
        if (empty($username) && empty($password)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        // ????????????????????????
        $user = UserService::getInstance()->getByUsername($username);
        if (is_null($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }

        // ?????????????????????
        $isPass = Hash::check($password, $user->getAuthPassword());
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT, '??????????????????');
        }

        // ?????????????????????
        $user->last_login_time = now()->toDateString();
        $user->last_login_ip = $request->getClientIp();
        if (!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }

        // ??????token
        $token = Auth::guard('wx')->login($user);

        // ?????????????????????
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
        // todo ????????????
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        // todo ????????????????????????
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        // todo ????????????????????????
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
        // ???????????????????????????
        UserService::getInstance()->checkCaptcha($mobile, $code);

        // ???????????????
        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = "https://thirdwx.qlogo.cn/mmopen/vi_32/ibFY0KibR500P0SjMKZsIN8rTy43AZ3qqTygmR1Lia0gjoLBnibXr3DPWHlF0yJMgNwH9sDAetkSkvC3VjIeTNjHdA/132";
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateString();
        $user->last_login_ip = $request->getClientIp();
        $user->save();

        // ???????????????
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
        // todo ???????????????
        $mobile = $request->input('mobile');
        // todo ???????????????????????????
        if (empty($mobile)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        // todo ??????????????????
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
        if ($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }

        // todo ??????????????????????????????
        $user = UserService::getInstance()->getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }

        // todo ???????????? , ??????????????????????????????
        $lock = Cache::add('register_captcha_lock_' . $mobile, 1, 60);
        if (!$lock) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);

        }

        // todo ????????????????????? 10 ???
        $isPass = UserService::getInstance()->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY, '?????????????????????????????????10???');

        }
        // ???????????????
        $code = UserService::getInstance()->setCaptcha($mobile);
        // ???????????????
        UserService::getInstance()->sendCaptchaMsg($mobile, $code);
        return $this->success();
    }
}
