<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServerInfoTest extends TestCase
{
    public function test_server_route_returns_json_structure(): void
    {
        $response = $this->get('/info/server');

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonStructure([
                     'php_version',
                     'server_software',
                 ]);
    }
}