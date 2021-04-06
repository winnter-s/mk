<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GoodsTest extends TestCase
{
    use DatabaseTransactions;

    public function testCount()
    {
        $this->assertLitemallApiGet('wx/goods/count');
    }

    public function testCategory()
    {
        $this->assertLitemallApiGet('wx/goods/category?id=1008009');
        $this->assertLitemallApiGet('wx/goods/category?id=1005000');

    }

    public function testList()
    {
        $this->assertLitemallApiGet('wx/goods/list');
//        $this->assertLitemallApiGet('wx/goods/list?categoryId=1008009');
//        $this->assertLitemallApiGet('wx/goods/list?brandId=1001000');
    }
}
