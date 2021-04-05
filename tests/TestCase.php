<?php

namespace Tests;

use GuzzleHttp\Client;
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
        return ['Authorization' => "Bearer {$token}"];
    }

    public function assertLitemallApiGet($uri, $ignore = [])
    {
        return $this->assertLitemallApi($uri, 'get', [], $ignore);
    }

    public function assertLitemallApiPost($uri, $data = [], $ignore = [])
    {
        return $this->assertLitemallApi($uri, 'post', $data, $ignore);
    }

    public function assertLitemallApi($uri, $method = 'get', $data = [], $ignore = [])
    {
        $client = new Client();
        if ($method == 'get') {
            $response1 = $this->get($uri, $this->getAuthHeader());
            $response2 = $client->get('http://106.54.87.148:8080/' . $uri,
                ['headers' => ['X-Litemall-Token' => $this->token]]);
        } else {
            $response1 = $this->post($uri, $this->getAuthHeader());
            $response2 = $client->post('http://106.54.87.148:8080/' . $uri,
                [
                    'headers' => ['X-Litemall-Token' => $this->token],
                    'json' => $data
                ]);
        }
        $content1 = $response1->getContent();
        $content1 = json_decode($content1, true);
        $content2 = $response2->getBody()->getContents();
        $content2 = json_decode($content2, true);

        foreach ($ignore as $key) {
            unset($content1[$key]);
            unset($content2[$key]);
        }

        $this->assertEquals($content2, $content1);
    }
}
