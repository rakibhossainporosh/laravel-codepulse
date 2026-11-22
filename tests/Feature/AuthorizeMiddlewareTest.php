<?php

namespace CodePulse\Tests;

use Illuminate\Support\Facades\Gate;

class AuthorizeMiddlewareTest extends TestCase
{
    public function test_dashboard_is_accessible_in_testing_environment(): void
    {
        $response = $this->get('/codepulse');
        $response->assertOk();
    }

    public function test_dashboard_is_blocked_when_disabled(): void
    {
        config(['codepulse.enabled' => false]);

        $response = $this->get('/codepulse');
        $response->assertNotFound();
    }
}
