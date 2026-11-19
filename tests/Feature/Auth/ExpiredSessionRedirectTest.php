<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class ExpiredSessionRedirectTest extends TestCase
{
    public function test_background_fetch_gets_401_and_does_not_set_intended_url(): void
    {
        $response = $this->withHeaders(['Sec-Fetch-Mode' => 'cors'])
            ->get('/notifications/count');

        $response->assertStatus(401);
        $this->assertNull(session('url.intended'));
    }

    public function test_json_request_gets_401(): void
    {
        $this->getJson('/notifications/count')->assertStatus(401);
    }

    public function test_page_navigation_still_redirects_to_login_and_remembers_page(): void
    {
        $response = $this->withHeaders(['Sec-Fetch-Mode' => 'navigate'])
            ->get('/notifications');

        $response->assertRedirect(route('login'));
        $this->assertSame(url('/notifications'), session('url.intended'));
    }
}
