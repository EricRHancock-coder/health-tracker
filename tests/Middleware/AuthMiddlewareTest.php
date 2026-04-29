<?php

namespace Tests\Middleware;

use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
use App\Utils\JwtHandler;
use App\Repositories\UserRepository;
use App\Utils\Response;
use App\Models\User;
use Exception;

class AuthMiddlewareTest extends TestCase
{
    private $jwtHandlerMock;
    private $userRepositoryMock;
    private $authMiddleware;

    protected function setUp(): void
    {
        $this->jwtHandlerMock = $this->createMock(JwtHandler::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        
        $this->authMiddleware = new AuthMiddleware(
            $this->jwtHandlerMock,
            $this->userRepositoryMock
        );
    }

    /**
     * Helper to simulate the Authorization header in $_SERVER
     */
    private function setAuthHeader(string $header): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = $header;
    }

    /**
     * Helper to clear the Authorization header
     */
    private function clearAuthHeader(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testHandleSuccess(): void
    {
        $this->setAuthHeader('Bearer valid.token.here');
        
        $mockUser = new User([
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'admin',
            'full_name' => 'Test User',
            'is_verified' => true,
            'is_disabled' => false
        ]);

        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willReturn(['sub' => 1]);

        $this->userRepositoryMock->method('findById')
            ->with(1)
            ->willReturn($mockUser);

        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertNull($result);
        $this->assertSame($mockUser, $requestContext['user']);
    }

    public function testHandleMissingHeader(): void
    {
        $this->clearAuthHeader();
        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('Authentication token required', $result->toArray()['error']);
    }

    public function testHandleInvalidHeaderFormat(): void
    {
        $this->setAuthHeader('InvalidFormat token');
        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('Authentication token required', $result->toArray()['error']);
    }

    public function testHandleInvalidToken(): void
    {
        $this->setAuthHeader('Bearer invalid.token');
        
        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willReturn(null);

        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('Invalid or expired token', $result->toArray()['error']);
    }

    public function testHandleUserNotFound(): void
    {
        $this->setAuthHeader('Bearer valid.token');
        
        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willReturn(['sub' => 999]);

        $this->userRepositoryMock->method('findById')
            ->with(999)
            ->willReturn(null);

        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('User not found', $result->toArray()['error']);
    }

    public function testHandleDisabledAccount(): void
    {
        $this->setAuthHeader('Bearer valid.token');
        
        $mockUser = new User([
            'id' => 1,
            'is_disabled' => true,
            'is_verified' => true
        ]);

        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willReturn(['sub' => 1]);

        $this->userRepositoryMock->method('findById')
            ->with(1)
            ->willReturn($mockUser);

        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals('Account is disabled', $result->toArray()['error']);
    }

    public function testHandleUnverifiedAccount(): void
    {
        $this->setAuthHeader('Bearer valid.token');
        
        $mockUser = new User([
            'id' => 1,
            'is_disabled' => false,
            'is_verified' => false
        ]);

        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willReturn(['sub' => 1]);

        $this->userRepositoryMock->method('findById')
            ->with(1)
            ->willReturn($mockUser);

        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals('Account not verified', $result->toArray()['error']);
    }

    public function testHandleException(): void
    {
        $this->setAuthHeader('Bearer token');
        
        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willThrowException(new Exception("Unexpected error"));

        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('Authentication failed', $result->toArray()['error']);
    }
}
