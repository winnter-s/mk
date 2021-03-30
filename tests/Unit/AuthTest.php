<?php

namespace Tests\Unit;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Services\UserService;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testCheckMobileSendCaptchaCount()
    {
        $mobile = '18802988922';
        foreach (range(0, 9) as $i) {
            $isPass = UserService::getInstance()->checkMobileSendCaptchaCount($mobile);
            $this->assertTrue($isPass);
        }
        $isPass = UserService::getInstance()->checkMobileSendCaptchaCount($mobile);
        $this->assertFalse($isPass);
    }

    public function testCheckCaptcha()
    {
        $mobile = '13111111197';
        $code = UserService::getInstance()->setCaptcha($mobile);
        $isPass = UserService::getInstance()->checkCaptcha($mobile, $code);
        $this->assertTrue($isPass);


        $this->expectExceptionObject(new BusinessException(CodeResponse::AUTH_CAPTCHA_UNMATCH));
        UserService::getInstance()->checkCaptcha($mobile, $code);
    }
}
