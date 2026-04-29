<?php

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Models\AuditLog;
use App\Repositories\AuditLogRepository;
use App\Utils\JwtHandler;
use App\Utils\Response;

class AuthControllerTest extends TestCase
{
  private $userRepositoryMock;
  private $auditRepositoryMock;
  private $jwtHandlerMock;
  private AuthController $authController;

  protected function setUp(): void
  {
    $this->userRepositoryMock = $this->createMock(UserRepository::class);
    $this->auditRepositoryMock = $this->createMock(AuditLogRepository::class);
    $this->jwtHandlerMock = $this->createMock(JwtHandler::class);

    $this->authController = new AuthController($this->userRepositoryMock, $this->auditRepositoryMock);

    // Use reflection to inject the mocked JwtHandler since it's instantiated in the constructor
    $reflection = new \ReflectionClass($this->authController);
    $property = $reflection->getProperty('jwtHandler');
    $property->setValue($this->authController, $this->jwtHandlerMock);
  }

  public function testLoginSuccess()
  {
    $userData = ['email' => 'user@test.com', 'password' => 'correct_password'];
    $user = new User([
      'id' => 1,
      'email' => 'user@test.com',
      'password_hash' => password_hash('correct_password', PASSWORD_BCRYPT),
      'role' => 'admin',
      'full_name' => 'Test User',
      'is_diabled' => 0,
      'is_verified' => 1
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
      ->with($this->isInstanceOf(AuditLog::class));

    $this->jwtHandlerMock->expects($this->once())
      ->method('generate')
      ->with($user)
      ->willReturn('mock_jwt_token');

    $response = $this->authController->login($userData);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('mock_jwt_token', $response->getBody());
  }

  public function testLoginInvalidEmailReturnsGenericError()
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

  public function testLoginWrongPasswordReturnsGenericError()
  {
    $userData = ['email' => 'user@test.com', 'password' => 'wrong_password'];
    $user = new User([
      'email' => 'user@test.com',
      'password_hash' => password_hash('correct_password', PASSWORD_BCRYPT)
    ]);

    $this->userRepositoryMock->method('findByEmail')->willReturn($user);

    $this->auditRepositoryMock->expects($this->once())
      ->method('save')
      ->with($this->callback(fn($log) => $log->action === 'FAILED_LOGIN'));

    $response = $this->authController->login($userData);

    $this->assertEquals(401, $response->getStatusCode());
    $this->assertStringContainsString('Invalid email or password', $response->getBody());
  }

  public function testLoginDisabledUserReturnsGenericError()
  {
    $userData = ['email' => 'disabled@test.com', 'password' => 'password'];
    $user = new User([
      'email' => 'disabled@test.com',
      'password_hash' => password_hash('password', PASSWORD_BCRYPT),
    ]);

    $this->userRepositoryMock->method('findByEmail')->willReturn($user);

    $this->auditRepositoryMock->expects($this->once())
      ->method('save')
      ->with($this->callback(fn($log) => $log->action === 'FAILED_LOGIN'));

    $response = $this->authController->login($userData);

    $this->assertEquals(401, $response->getStatusCode());
    $this->assertStringContainsString('Invalid email or password', $response->getBody());
  }

  public function testLogout()
  {
    $response = $this->authController->logout();

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('DISCARD_TOKEN', $response->getBody());
  }
}
