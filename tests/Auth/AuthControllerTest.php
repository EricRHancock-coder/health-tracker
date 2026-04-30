<?php

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Repositories\UserRepository;
use App\Repositories\AuditLogRepository;
use App\Utils\JwtHandler;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class AuthControllerTest extends TestCase
{
    private $userRepositoryMock;
    private $auditRepositoryMock;
    private $jwtHandlerMock;
    private AuthController $authController;

    protected function setUp(): void
    {
        if (!R::testConnection()) {
            R::setup('sqlite::memory:');
        }

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->auditRepositoryMock = $this->createMock(AuditLogRepository::class);
        $this->jwtHandlerMock = $this->createMock(JwtHandler::class);

        $this->authController = new AuthController(
            $this->userRepositoryMock,
            $this->auditRepositoryMock
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
}
