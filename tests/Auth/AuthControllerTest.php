<?php

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Repositories\UserRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\BlacklistRepository;
use App\Utils\JwtHandler;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class AuthControllerTest extends TestCase
{
    private $userRepositoryMock;
    private $auditRepositoryMock;
    private $blacklistRepositoryMock;
    private $jwtHandlerMock;
    private AuthController $authController;

    protected function setUp(): void
    {
        if (!R::testConnection()) {
            R::setup('sqlite::memory:');
        }

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->auditRepositoryMock = $this->createMock(AuditLogRepository::class);
        $this->blacklistRepositoryMock = $this->createMock(BlacklistRepository::class);
        $this->jwtHandlerMock = $this->createMock(JwtHandler::class);

        $this->authController = new AuthController(
            $this->userRepositoryMock,
            $this->auditRepositoryMock,
            $this->blacklistRepositoryMock
        );

        // The controller instantiates its own JwtHandler in the constructor; swap
        // in the mock via reflection so we can assert against it.
        $reflection = new \ReflectionClass($this->authController);
        $property = $reflection->getProperty('jwtHandler');
        $property->setValue($this->authController, $this->jwtHandlerMock);
    }

    protected function tearDown(): void
    {
        R::nuke();
    }

    private function makeUserBean(array $data): OODBBean
    {
        $user = R::dispense('users');
        foreach ($data as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    public function testLoginSuccess(): void
    {
        $userData = ['email' => 'user@test.com', 'password' => 'correct_password'];
        $user = $this->makeUserBean([
            'id' => 1,
            'email' => 'user@test.com',
            'password_hash' => password_hash('correct_password', PASSWORD_BCRYPT),
            'role' => 'admin',
            'full_name' => 'Test User',
            'is_disabled' => 0,
            'is_verified' => 1,
        ]);

        $this->blacklistRepositoryMock->expects($this->once())
            ->method('cleanup');

        $this->userRepositoryMock->expects($this->once())
            ->method('findByEmail')
            ->with('user@test.com')
            ->willReturn($user);

        $this->userRepositoryMock->expects($this->once())
            ->method('update')
            ->with($this->callback(fn($u) => $u->last_login_at !== null));

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(OODBBean::class));

        $this->jwtHandlerMock->expects($this->once())
            ->method('generate')
            ->with($user)
            ->willReturn('mock_jwt_token');

        $response = $this->authController->login($userData);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('mock_jwt_token', $response->getBody());
    }

    public function testLoginSuccessCapturesIpAddress(): void
    {
        $capturedLog = null;
        $userData = ['email' => 'user@test.com', 'password' => 'correct_password'];
        $user = $this->makeUserBean([
            'id' => 1,
            'email' => 'user@test.com',
            'password_hash' => password_hash('correct_password', PASSWORD_BCRYPT),
            'role' => 'admin',
            'full_name' => 'Test User',
            'is_disabled' => 0,
            'is_verified' => 1,
        ]);

        $this->blacklistRepositoryMock->method('cleanup');

        $this->userRepositoryMock->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->userRepositoryMock->method('update');

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($log) use (&$capturedLog) {
                $capturedLog = $log;
                return 1;
            });

        $this->jwtHandlerMock->method('generate')->willReturn('mock_token');

        // Set a mock IP for testing
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';

        $this->authController->login($userData);

        $this->assertNotNull($capturedLog);
        $this->assertEquals('192.168.1.100', $capturedLog->ip_address);
        $this->assertEquals('LOGIN', $capturedLog->action);
    }

    public function testLoginInvalidEmailReturnsGenericError(): void
    {
        $userData = ['email' => 'nonexistent@test.com', 'password' => 'any_password'];

        $this->blacklistRepositoryMock->method('cleanup');

        $this->userRepositoryMock->method('findByEmail')->willReturn(null);

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(fn($log) => $log->action === 'FAILED_LOGIN'));

        $response = $this->authController->login($userData);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Invalid email or password', $response->getBody());
    }

    public function testLoginFailureCapturesIpAddress(): void
    {
        $capturedLog = null;
        $userData = ['email' => 'nonexistent@test.com', 'password' => 'any_password'];

        $this->blacklistRepositoryMock->method('cleanup');

        $this->userRepositoryMock->method('findByEmail')->willReturn(null);

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($log) use (&$capturedLog) {
                $capturedLog = $log;
                return 1;
            });

        // Set a mock IP for testing
        $_SERVER['REMOTE_ADDR'] = '10.0.0.50';

        $this->authController->login($userData);

        $this->assertNotNull($capturedLog);
        $this->assertEquals('10.0.0.50', $capturedLog->ip_address);
        $this->assertEquals('FAILED_LOGIN', $capturedLog->action);
        $this->assertStringContainsString('User not found', $capturedLog->old_values);
    }

    public function testLoginWrongPasswordReturnsGenericError(): void
    {
        $userData = ['email' => 'user@test.com', 'password' => 'wrong_password'];
        $user = $this->makeUserBean([
            'email' => 'user@test.com',
            'password_hash' => password_hash('correct_password', PASSWORD_BCRYPT),
            'is_verified' => 1,
            'is_disabled' => 0,
        ]);

        $this->blacklistRepositoryMock->method('cleanup');

        $this->userRepositoryMock->method('findByEmail')->willReturn($user);

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(fn($log) => $log->action === 'FAILED_LOGIN'));

        $response = $this->authController->login($userData);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Invalid email or password', $response->getBody());
    }

    public function testLoginDisabledUserReturnsGenericError(): void
    {
        $userData = ['email' => 'disabled@test.com', 'password' => 'password'];
        $user = $this->makeUserBean([
            'email' => 'disabled@test.com',
            'password_hash' => password_hash('password', PASSWORD_BCRYPT),
            'is_verified' => 0,
            'is_disabled' => 1,
        ]);

        $this->blacklistRepositoryMock->method('cleanup');

        $this->userRepositoryMock->method('findByEmail')->willReturn($user);

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(fn($log) => $log->action === 'FAILED_LOGIN'));

        $response = $this->authController->login($userData);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Invalid email or password', $response->getBody());
    }

    public function testLoginMissingCredentialsReturnsGenericError(): void
    {
        $this->blacklistRepositoryMock->method('cleanup');

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(fn($log) => $log->action === 'FAILED_LOGIN'));

        $response = $this->authController->login([]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('Invalid email or password', $response->getBody());
    }

    public function testLogout(): void
    {
        $response = $this->authController->logout();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('DISCARD_TOKEN', $response->getBody());
    }

    public function testLogoutAddsTokenToBlacklist(): void
    {
        $token = 'valid.jwt.token';
        $payload = ['sub' => 1, 'exp' => time() + 3600];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->jwtHandlerMock->method('verifyAndDecode')
            ->with($token)
            ->willReturn($payload);

        $this->blacklistRepositoryMock->expects($this->once())
            ->method('add')
            ->with(
                hash('sha256', $token),
                $this->stringContains(gmdate('Y-m-d H:i:s', $payload['exp']))
            );

        $this->blacklistRepositoryMock->expects($this->once())
            ->method('cleanup');

        $this->auditRepositoryMock->method('save');

        $this->authController->logout();
    }

    public function testLogoutLogsAuditEvent(): void
    {
        $token = 'valid.jwt.token';
        $payload = ['sub' => 1, 'exp' => time() + 3600];
        $capturedLog = null;

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        $_SERVER['REMOTE_ADDR'] = '192.168.1.50';

        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willReturn($payload);

        $this->blacklistRepositoryMock->method('add');
        $this->blacklistRepositoryMock->method('cleanup');

        $this->auditRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($log) use (&$capturedLog) {
                $capturedLog = $log;
                return 1;
            });

        $this->authController->logout();

        $this->assertNotNull($capturedLog);
        $this->assertEquals('LOGOUT', $capturedLog->action);
        $this->assertEquals(1, $capturedLog->user_id);
        $this->assertEquals('192.168.1.50', $capturedLog->ip_address);
    }

    public function testLogoutWithoutAuthHeaderStillReturnsSuccess(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);

        // No mocks should be called - just return success
        $response = $this->authController->logout();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('DISCARD_TOKEN', $response->getBody());
    }

    public function testLogoutWithInvalidTokenStillReturnsSuccess(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid.token';

        $this->jwtHandlerMock->method('verifyAndDecode')
            ->willReturn(null);

        // Should still return success even if token is invalid
        $response = $this->authController->logout();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('DISCARD_TOKEN', $response->getBody());
    }
}
