<?php

namespace Tests\Feature\Demo;

use Tests\TestCase;

class DemoHeadersTest extends TestCase
{
    public function test_demo_web_responses_are_not_indexable(): void
    {
        config()->set('demo.enabled', true);

        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_local_web_responses_do_not_force_a_robots_header(): void
    {
        config()->set('demo.enabled', false);

        $this->get('/login')
            ->assertOk()
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_demo_api_responses_do_not_receive_the_web_only_header(): void
    {
        config()->set('demo.enabled', true);

        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeaderMissing('X-Robots-Tag');
    }
}
