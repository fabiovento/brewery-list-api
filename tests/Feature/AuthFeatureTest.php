<?php

namespace Tests\Feature;

use App\Services\AuthService;
use Tests\TestCase;
use Illuminate\Auth\AuthenticationException;

class AuthFeatureTest extends TestCase
{
    public function test_login_successful()
    {
        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('login')
                ->once()
                ->with('root@root.com', 'password')
                ->andReturn([
                    'token' => '46|gn8YUYGxergaDTHzF1dyA9NsX26B9CtE8CCmzRR08f6e685f'
                ]);
        });

        $response = $this->postJson('http://localhost/api/login', [
            'email' => 'root@root.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
            'access_token' => '46|gn8YUYGxergaDTHzF1dyA9NsX26B9CtE8CCmzRR08f6e685f',
            'token_type' => 'Bearer',
        ]);
    }

    public function test_login_failed()
    {
        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('login')
                ->once()
                ->with('root@root.com', 'wrong_password')
                ->andThrow(new AuthenticationException());
        });

        $response = $this->postJson('http://localhost/api/login', [
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
