<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    protected $token;

    public function getAuthHeader()
    {
        $response = $this->post('wx/auth/login',
            [
                'username' => 'user123',
                'password' => 'user123'
            ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $this->token = $token;
        return ['Authorization'=>"Bearer {$token}"];
    }
}
