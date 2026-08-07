<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoLoginPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_lists_demo_accounts_when_demo_mode_is_on(): void
    {
        config(['demo.enabled' => true]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Demo accounts');
        $response->assertSee('admin@imprint.ph');
        $response->assertSee('events@imprint.ph');
    }

    public function test_the_login_page_hides_credentials_by_default(): void
    {
        // The LAN installation never sets DEMO_MODE, and these are real staff
        // accounts there — the page must not advertise them.
        config(['demo.enabled' => false]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertDontSee('Demo accounts');
        $response->assertDontSee('admin@imprint.ph');
        $response->assertSee('Accounts are provisioned by an administrator.');
    }

    public function test_demo_mode_is_off_unless_explicitly_enabled(): void
    {
        $this->assertFalse(config('demo.enabled'));
    }
}
