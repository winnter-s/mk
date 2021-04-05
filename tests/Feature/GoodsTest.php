<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BrandTest extends TestCase
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
}
