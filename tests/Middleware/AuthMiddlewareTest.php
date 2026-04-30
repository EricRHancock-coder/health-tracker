<?php

namespace Tests\Middleware;

use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
use App\Utils\JwtHandler;
use App\Repositories\UserRepository;
use App\Utils\Response;
use Exception;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class AuthMiddlewareTest extends TestCase
{
    private $jwtHandlerMock;
    private $userRepositoryMock;
    private $authMiddleware;

    protected function setUp(): void
    {
        if (!R::testConnection()) {
            R::setup('sqlite::memory:');
        }

        $this->jwtHandlerMock = $this->createMock(JwtHandler::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        $this->authMiddleware = new AuthMiddleware(
            $this->jwtHandlerMock,
            $this->userRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        R::nuke();
    }

    private function setAuthHeader(string $header): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = $header;
    }

    private function clearAuthHeader(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    private function makeUserBean(array $data): OODBBean
    {
        $user = R::dispense('users');
        foreach ($data as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    public function testHandleSuccess(): void
    {
        $this->setAuthHeader('Bearer valid.token.here');

        $mockUser = $this->makeUserBean([
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'admin',
            'full_name' => 'Test User',
            'is_verified' => 1,
            'is_disabled' => 0,
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

        $mockUser = $this->makeUserBean([
            'id' => 1,
            'is_disabled' => 1,
            'is_verified' => 1,
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

        $mockUser = $this->makeUserBean([
            'id' => 1,
            'is_disabled' => 0,
            'is_verified' => 0,
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
            ->willThrowException(new Exception('Unexpected error'));

        $requestContext = [];
        $result = $this->authMiddleware->handle($requestContext);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('Authentication failed', $result->toArray()['error']);
    }
}
