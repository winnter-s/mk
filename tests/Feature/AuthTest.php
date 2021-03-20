<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions; // 执行的数据库操作 都不会提交
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testRegister()
    {
        $response = $this->post('wx/auth/register',[
            'username'=>'tanfan7',
            'password'=>'123456',
            'mobile'=>'13456789126',
            'code'=>'1234'
        ]);
        $response->assertStatus(200);
        // $response->getContent() // 调取json值
        $ret = $response->getOriginalContent(); // 调取原始值
        $this->assertEquals(0,$ret['errno']); // 相等断言
        $this->assertNotEmpty($ret['data']); // 不为空
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
}
