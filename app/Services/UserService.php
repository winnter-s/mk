<?php


namespace App\Services;


use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\User\User;
use App\Notifications\VerificationCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class UserService extends BaseService
{
    /**
     * 根据用户名获取用户
     * @param $username
     * @return User|Builder|Model|object|null
     */
    public function getByUsername(string $username)
    {
        return User::query()
            ->where('username', $username)
            ->where('deleted', 0)
            ->first();
    }

    /**
     * 根据手机号获取用户
     * @param $mobile
     * @return Builder|Model|object|null
     */
    public function getByMobile(string $mobile)
    {
        return User::query()
            ->where('mobile', $mobile)
            ->where('deleted', 0)
            ->first();
    }

    public function checkMobileSendCaptchaCount(string $mobile)
    {
        $countKey = 'register_captcha_count_' . $mobile;
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;
            }
        } else {
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }
        return true;
    }

    public function sendCaptchaMsg(string $mobile, string $code)
    {
        // 如果单元测试 就不发送短信
        if (app()->environment('testing')) {
            return;
        }
        // todo 发送短信
        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber($mobile, 86)
        )->notify(new VerificationCode($code));
        return ['errno' => 0, 'errmsg' => '成功', 'data' => null];
    }

    // 验证短信验证码
    public function checkCaptcha(string $mobile, string $code)
    {
        if(!app()->environment('production')){
            return true;
        }
        $key = 'register_captcha_' . $mobile;
        $isPass = $code === Cache::get($key);
        if ($isPass) {
            Cache::forget($key);
            return true;
        } else {
            throw new BusinessException(CodeResponse::AUTH_CAPTCHA_UNMATCH);
        }

    }

    /**
     * 设置手机验证码
     * @param string $mobile
     * @return int
     */
    public function setCaptcha(string $mobile)
    {
        // todo 保存手机号和验证码的关系
        // todo 随机生成 6 为验证码
        $code = random_int(100000, 999999);
        $code = strval($code);
        Cache::put('register_captcha_' . $mobile, $code, 600);
        return $code;
    }
}
