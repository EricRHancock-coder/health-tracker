<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\BlacklistRepository;
use App\Utils\Response;
use App\Utils\JwtHandler;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

/**
 * AuthController
 *
 * Handles authentication lifecycle.
 */
class AuthController
{
  private UserRepository $userRepository;
  private AuditLogRepository $auditLogRepository;
  private BlacklistRepository $blacklistRepository;
  private JwtHandler $jwtHandler;

  public function __construct(
    UserRepository $userRepository,
    AuditLogRepository $auditLogRepository,
    BlacklistRepository $blacklistRepository
  ) {
    $this->userRepository = $userRepository;
    $this->auditLogRepository = $auditLogRepository;
    $this->blacklistRepository = $blacklistRepository;
    $authConfig = require __DIR__ . '/../config/auth.php';
    $this->jwtHandler = new JwtHandler($authConfig);
  }

  /**
   * Authenticates a user and returns a JWT.
   */
  public function login(array $data): Response
  {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($email === '' || $password === '') {
      $this->logAuthFailure((string) $email, 'Missing credentials');
      return Response::json(['error' => 'Invalid email or password'], 401);
    }

    $user = $this->userRepository->findByEmail($email);

    if (!$user) {
      $this->logAuthFailure($email, 'User not found');
      return Response::json(['error' => 'Invalid email or password'], 401);
    }

    if (!password_verify($password, (string) $user->password_hash)) {
      $this->logAuthFailure($email, 'Incorrect password');
      return Response::json(['error' => 'Invalid email or password'], 401);
    }

    if (!$user->isActive()) {
      $this->logAuthFailure($email, 'Attempt to login to disabled account');
      return Response::json(['error' => 'Invalid email or password'], 401);
    }

    // Cleanup expired tokens on login (handles abandoned sessions)
    $this->blacklistRepository->cleanup();

    $token = $this->jwtHandler->generate($user);
    $this->logAuthSuccess($user);

    $user->last_login_at = gmdate('Y-m-d H:i:s');
    $this->userRepository->update($user);

    return Response::json([
      'token' => $token,
      'user' => [
        'id' => (int) $user->id,
        'email' => (string) $user->email,
        'role' => (string) $user->role,
        'full_name' => (string) $user->full_name,
      ],
    ]);
  }

  /**
   * Handles logout with token blacklisting.
   *
   * Extracts token from Authorization header, adds to blacklist,
   * cleans up expired tokens, and logs the logout event.
   */
  public function logout(): Response
  {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (str_starts_with($authHeader, 'Bearer ')) {
      $token = substr($authHeader, 7);
      $payload = $this->jwtHandler->verifyAndDecode($token);

      if ($payload) {
        // Add token to blacklist until its original expiry (UTC)
        $expiresAt = gmdate('Y-m-d H:i:s', $payload['exp']);
        $tokenHash = hash('sha256', $token);
        $this->blacklistRepository->add($tokenHash, $expiresAt);

        // Cleanup expired tokens
        $this->blacklistRepository->cleanup();

        // Log the logout event
        $this->logLogout($payload['sub']);
      }
    }

    return Response::json([
      'message' => 'Successfully logged out',
      'instruction' => 'DISCARD_TOKEN',
    ], 200, [
      'X-Logout-Instruction' => 'discard-token',
    ]);
  }

  private function logAuthFailure(string $identifier, string $reason): void
  {
    $payload = json_encode(['reason' => $reason, 'identifier' => $identifier]);

    $log = R::dispense('audit_log');
    $log->action = 'FAILED_LOGIN';
    $log->old_values = $payload;
    $log->new_values = $payload;
    $log->timestamp = gmdate('Y-m-d H:i:s');
    $log->ip_address = $_SERVER['REMOTE_ADDR'];

    $this->auditLogRepository->save($log);
  }

  private function logAuthSuccess(OODBBean $user): void
  {
    $log = R::dispense('audit_log');
    $log->action = 'LOGIN';
    $log->user_id = (int) $user->id;
    $log->new_values = json_encode(['email' => (string) $user->email]);
    $log->timestamp = gmdate('Y-m-d H:i:s');
    $log->ip_address = $_SERVER['REMOTE_ADDR'];

    // NOTE: table_name and record_id are intentionally left null for LOGIN events.
    // These fields track data modifications (CRUD operations). LOGIN is an authentication
    // event that doesn't modify any database record. The user_id field already identifies
    // who performed the action. CRUD operations on users/residents should populate
    // table_name and record_id to indicate what was modified.

    $this->auditLogRepository->save($log);
  }

  private function invalidateServerSideSession(): void
  {
    // Placeholder for blacklist logic
  }

  /**
   * Logs a successful logout event.
   */
  private function logLogout(int $userId): void
  {
    $log = R::dispense('audit_log');
    $log->action = 'LOGOUT';
    $log->user_id = $userId;
    $log->timestamp = gmdate('Y-m-d H:i:s');
    $log->ip_address = $_SERVER['REMOTE_ADDR'];

    $this->auditLogRepository->save($log);
  }
}
