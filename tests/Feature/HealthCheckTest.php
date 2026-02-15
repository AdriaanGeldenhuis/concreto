<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'checks' => ['database', 'queue', 'cache', 'storage'],
            'timestamp',
        ]);
        $response->assertJson(['status' => 'healthy']);
    }
}
