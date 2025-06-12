<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_access_protected_route()
    {
        $loginResponse = $this->postJson('http://localhost/api/login', [
            'email' => 'root@root.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $this->assertNotEmpty($token, 'Token non ricevuto dal login.');

        $listResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('http://localhost/api/list?page=1');

        $listResponse->assertStatus(200);
    }

    public function test_login_with_invalid_credentials_fails()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'root@root.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'status' => 'error',
            'code' => 401,
        ]);
    }
}
