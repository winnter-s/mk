<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use DatabaseTransactions;

    public function testList()
    {
        $response = $this->get('wx/address/list', $this->getAuthHeader());

        $client = new Client();
        $response2 = $client->get('http://106.54.87.148:8080/wx/address/list',
            ['headers' => ['X-Litemall-Token' =>$this->token]]
        );
        $list = json_decode($response2->getBody()->getContents(), true);
        $response->assertJson($list);
    }


}
