<?php

namespace Tests\Feature;

use App\Models\Address;
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

    public function testDelete()
    {
        $address = Address::query()->first();
        $this->assertNotEmpty($address->toArray());
        $response = $this->post('wx/address/delete',['id'=>$address->id],$this->getAuthHeader());
        $response->assertJson(['errno'=>0]);
        $address = Address::query()->find($address->id);
        $this->assertEmpty($address);
    }


}
