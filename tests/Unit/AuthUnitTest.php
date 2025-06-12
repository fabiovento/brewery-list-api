<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\AuthController;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;
use Mockery;

class AuthUnitTest extends TestCase
{
    public function test_login_successful()
    {
        $mockAuthService = Mockery::mock(AuthService::class);
        $mockAuthService->shouldReceive('login')
            ->once()
            ->with('root@root.com', 'password')
            ->andReturn(['token' => '46|gn8YUYGxergaDTHzF1dyA9NsX26B9CtE8CCmzRR08f6e685f']);

        $controller = new AuthController($mockAuthService);

        $request = new Request([
            'email' => 'root@root.com',
            'password' => 'password',
        ]);

        $response = $controller->login($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getData(true);

        $this->assertEquals('ok', $responseData['status']);
        $this->assertEquals('46|gn8YUYGxergaDTHzF1dyA9NsX26B9CtE8CCmzRR08f6e685f', $responseData['access_token']);
        $this->assertEquals('Bearer', $responseData['token_type']);
    }

    public function test_login_failed()
    {
        $mockAuthService = Mockery::mock(AuthService::class);
        $mockAuthService->shouldReceive('login')
            ->once()
            ->with('root@root.com', 'wrong_password')
            ->andThrow(new AuthenticationException());

        $controller = new AuthController($mockAuthService);

        $request = new Request([
            'email' => 'root@root.com',
            'password' => 'wrong_password',
        ]);

        $this->expectException(AuthenticationException::class);

        $controller->login($request);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
