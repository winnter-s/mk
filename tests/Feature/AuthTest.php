<?php

namespace Tests\Feature;

use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions; // 执行的数据库操作 都不会提交

    public function testRegister()
    {
        $code = UserService::getInstance()->setCaptcha('13456789126');
        $response = $this->post('wx/auth/register',[
            'username'=>'tanfan7',
            'password'=>'123456',
            'mobile'=>'13456789126',
            'code'=>$code
        ]);
        $response->assertStatus(200);
        // $response->getContent() // 调取json值
        $ret = $response->getOriginalContent(); // 调取原始值
        $this->assertEquals(0,$ret['errno']); // 相等断言
        $this->assertNotEmpty($ret['data']); // 不为空
    }

    public function testRegisterErrCode()
    {
        $response = $this->post('wx/auth/register',[
            'username'=>'tanfan7',
            'password'=>'123456',
            'mobile'=>'13456789127',
            'code'=>'1234'
        ]);

        $response->assertJson([
            'errno'=>703,
            'errmsg'=>'验证码不正确'
        ]);
    }

    /**
     * 验证手机号码错误 返回错误码 707
     */
    public function testRegisterMobile()
    {
        $response = $this->post('wx/auth/register',[
            'username'=>'tanfan8',
            'password'=>'123456',
            'mobile'=>'134567891267',
            'code'=>'1234'
        ]);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent(); // 调取原始值
        $this->assertEquals(707,$ret['errno']); //
    }

    public function testRegCaptcha()
    {
        $response = $this->post('wx/auth/regCaptcha',['mobile'=>'13111111119']);
        $response->assertJson(['errno'=>0,'errmsg'=>'成功']);
    }
}
