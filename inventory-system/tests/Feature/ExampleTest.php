<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_root_redirects_to_inventory(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/inventory');
    }

    public function test_the_inventory_page_returns_a_successful_response(): void
    {
        $response = $this->get('/inventory');

        $response->assertStatus(200);
    }
}
