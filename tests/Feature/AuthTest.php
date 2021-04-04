<?php

namespace Tests\Feature;

use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    // 执行的数据库操作 都不会提交

    public function testRegister()
    {
        $code = UserService::getInstance()->setCaptcha('13456789126');
        $response = $this->post('wx/auth/register', [
            'username' => 'tanfan7',
            'password' => '123456',
            'mobile' => '13456789126',
            'code' => $code
        ]);
        $response->assertStatus(200);
        // $response->getContent() // 调取json值
        $ret = $response->getOriginalContent(); // 调取原始值
        $this->assertEquals(0, $ret['errno']); // 相等断言
        $this->assertNotEmpty($ret['data']); // 不为空
    }

    public function testRegisterErrCode()
    {
        $response = $this->post('wx/auth/register', [
            'username' => 'tanfan7',
            'password' => '123456',
            'mobile' => '13456789127',
            'code' => '1234'
        ]);

        $response->assertJson([
            'errno' => 703,
            'errmsg' => '验证码不正确'
        ]);
    }

    /**
     * 验证手机号码错误 返回错误码 707
     */
    public function testRegisterMobile()
    {
        $response = $this->post('wx/auth/register', [
            'username' => 'tanfan8',
            'password' => '123456',
            'mobile' => '134567891267',
            'code' => '1234'
        ]);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent(); // 调取原始值
        $this->assertEquals(707, $ret['errno']); //
    }

    public function testRegCaptcha()
    {
        $response = $this->post('wx/auth/regCaptcha', ['mobile' => '13111111119']);
        $response->assertJson(['errno' => 0, 'errmsg' => '成功']);
    }

    public function testLogin()
    {
        $response = $this->post('wx/auth/login', [
            'username' => 'tanfan5',
            'password' => '123456',
        ]);
        echo $response->getOriginalContent()['data']['token'] ?? '';
        $this->assertNotEmpty($response->getOriginalContent()['data']['token'] ?? '');
//        dd($response->getOriginalContent());
    }

    public function testInfo()
    {
        $response = $this->post('wx/auth/login',
            [
                'username' => 'user123',
                'password' => 'user123'
            ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $res = $this->get('wx/auth/info', [
            'Authorization' => "Bearer {$token}"
        ]);
        $user = UserService::getInstance()->getByUsername('user123');
        $res->assertJson([
            'data' => [
                'nickName' => $user->nickname,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
                'mobile' => $user->mobile
            ]
        ]);
    }

    public function testLogout()
    {
        $response = $this->post('wx/auth/login',
            [
                'username' => 'user123',
                'password' => 'user123'
            ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $res = $this->get('wx/auth/info', [
            'Authorization' => "Bearer {$token}"
        ]);
        $user = UserService::getInstance()->getByUsername('user123');
        $res->assertJson([
            'data' => [
                'nickName' => $user->nickname,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
                'mobile' => $user->mobile
            ]
        ]);

        $res2 = $this->post('wx/auth/logout', [], ['Authorization' => "Bearer {$token}"]);
        $res2->assertJson(['errno' => 0]);
        $res3 = $this->get('wx/auth/info', ['Authorization' => "Bearer {$token}"]);
        $res3->assertJson(['errno' => 501]);
    }

    public function testRest()
    {
        $mobile = '13456789124';
        $code = UserService::getInstance()->setCaptcha($mobile);
        $response = $this->post('wx/auth/reset',
            [
                'mobile' => $mobile,
                'password' => '123456',
                'code' => $code
            ]);
        $response->assertJson(['errno' => 0]);
        $user = UserService::getInstance()->getByMobile($mobile);
        $isPass = Hash::check('123456', $user->password);
        $this->assertTrue($isPass);
    }

    public function testProfile()
    {
        $response = $this->post('wx/auth/login',
            [
                'username' => 'user123',
                'password' => 'user123'
            ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $response = $this->post('wx/auth/profile',
            [
                'avatar' => '',
                'gender' => 1,
                'nickname' => 'user1234'
            ], ['Authorization' => "bearer {$token}"]);
        $response->assertJson(['errno' => 0]);
        $user = UserService::getInstance()->getByUsername('user123');
        $this->assertEquals('user1234', $user->nickname);
        $this->assertEquals(1, $user->gender);
    }
}
