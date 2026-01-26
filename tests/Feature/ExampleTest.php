<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that home page redirects to login when not authenticated.
     */
    public function test_home_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get('/');

        // Should redirect to login page when not authenticated
        $response->assertRedirect('/login');
    }

    /**
     * Test that login page is accessible.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
