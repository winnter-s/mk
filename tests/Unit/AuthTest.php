<?php

namespace Tests\Unit;

use App\Services\UserService;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testCheckMobileSendCaptchaCount()
    {
        $mobile = '18802988922';
        foreach(range(0,9) as $i) {
            $isPass = (new UserService())->checkMobileSendCaptchaCount($mobile);
            $this->assertTrue($isPass);
        }
        $isPass = (new UserService())->checkMobileSendCaptchaCount($mobile);
        $this->assertFalse($isPass);
    }

    public function testCheckCaptcha()
    {
        $mobile = '13111111111';
        $code = (new UserService())->setCaptcha($mobile);
        $isPass = (new UserService())->checkCaptcha($mobile,$code);
        $this->assertTrue($isPass);
    }
}
