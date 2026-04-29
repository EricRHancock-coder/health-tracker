<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\AuditLog as AuditLogModel;
use App\Repositories\UserRepository;
use App\Repositories\AuditLogRepository;
use App\Utils\Response;
use App\Utils\Validator;
use App\Utils\JwtHandler;

/**
 * AuthController
 * 
 * Handles authentication lifecycle.
 */
class AuthController
{
  private UserRepository $userRepository;
  private AuditLogRepository $auditLogRepository;
  private JwtHandler $jwtHandler;

  public function __construct(UserRepository $userRepository, AuditLogRepository $auditLogRepository)
  {
    $this->userRepository = $userRepository;
    $this->auditLogRepository = $auditLogRepository;
    $authConfig = require __DIR__ . '/../config/auth.php';
    $this->jwtHandler = new JwtHandler($authConfig);
  }


  /**
   * Authenticates a user and returns a JWT.
   */
  public function login(array $data): Response
  {
    // 1. Validate Input
    /* $validator = new Validator($data);
        if (!$validator->validate(['email' => 'required|email', 'password' => 'required'])) {
            // Log validation failure and return generic 401
            $this->logAuthFailure('N/A', 'Validation failed: ' . implode(', ', $validator->getErrors()));
            return Response::json(['error' => 'Invalid email or password'], 401);
        }
       */

    $email = $data['email'];
    $password = $data['password'];

    // 2. Retrieve User
    $user = $this->userRepository->findByEmail($email);

    // CASE: User not found
    if (!$user) {
      $this->logAuthFailure($email, 'User not found');
      return Response::json(['error' => 'Invalid email or password'], 401);
      //Response::json(['error' => 'Invalid email or password'], 401);
    }

    // CASE: Incorrect password
    if (!password_verify($password, $user->password_hash)) {
      $this->logAuthFailure($email, 'Incorrect password');
      return Response::json(['error' => 'Invalid email or password'], 401);
    }

    // CASE: Account disabled
    if (!$user->isActive()) {
      $this->logAuthFailure($email, 'Attempt to login to disabled account');
      return Response::json(['error' => 'Invalid email or password'], 401);
    }

    // 3. Successful Authentication
    $token = $this->jwtHandler->generate($user);
    $this->logAuthSuccess($user);

    // 4. Update User State
    $user->last_login_at = date('Y-m-d H:i:s');
    $this->userRepository->update($user);

    return Response::json([
      'token' => $token,
      'user' => [
        'id' => $user->id,
        'email' => $user->email,
        'role' => $user->role,
        'full_name' => $user->full_name
      ]
    ]);
  }

  /**
   * Handles logout.
   */
  public function logout(): Response
  {
    // 1. Server-side invalidation (Placeholder)
    $this->invalidateServerSideSession();

    // 2. Return response with instructions for the client
    return Response::json([
      'message' => 'Successfully logged out',
      'instruction' => 'DISCARD_TOKEN'
    ], 200, [
      'X-Logout-Instruction' => 'discard-token'
    ]);
  }

  private function logAuthFailure(string $identifier, string $reason): void
  {
    $log = new AuditLogModel([
      'action' => 'FAILED_LOGIN',
      'old_values' => json_encode(['reason' => $reason, 'identifier' => $identifier]),
      'new_values' => json_encode(['reason' => $reason, 'identifier' => $identifier])
    ]);
    $this->auditLogRepository->save($log);
  }

  private function logAuthSuccess(User $user): void
  {
    $log = new AuditLogModel([
      'action' => 'LOGIN',
      'user_id' => $user->id,
      'new_values' => json_encode(['email' => $user->email])
    ]);
    $this->auditLogRepository->save($log);
  }

  private function invalidateServerSideSession(): void
  {
    // Placeholder for blacklist logic
  }
}
