<?php

namespace Tests\Unit;

use App\Services\UrlEgressPolicy;
use Tests\TestCase;

class UrlEgressPolicyTest extends TestCase
{
    public function test_blocks_localhost_and_private_ips(): void
    {
        $policy = new UrlEgressPolicy;

        $this->expectException(\InvalidArgumentException::class);
        $policy->assertAllowed('https://127.0.0.1/');
    }

    public function test_blocks_userinfo_urls(): void
    {
        $policy = new UrlEgressPolicy;

        $this->expectException(\InvalidArgumentException::class);
        $policy->assertAllowed('https://user:pass@example.com/');
    }
}

