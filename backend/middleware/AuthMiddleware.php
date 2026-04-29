<?php

namespace App\Middleware;

use App\Utils\JwtHandler;
use App\Repositories\UserRepository;
use App\Utils\Response;
use Exception;

/**
 * AuthMiddleware
 * 
 * Validates JWT tokens and ensures the authenticated user is active and verified.
 * Injects the authenticated User object into the request context.
 */
class AuthMiddleware
{
  private JwtHandler $jwtHandler;
  private UserRepository $userRepository;

  public function __construct(JwtHandler $jwtHandler, UserRepository $userRepository)
  {
    $this->jwtHandler = $jwtHandler;
    $this->userRepository = $userRepository;
  }

  /**
   * Handles the authentication check for a request.
   * 
   * @param array &$requestContext The application's request context (passed by reference)
   * @return Response|null Returns a Response object if authentication fails, null otherwise.
   */
  public function handle(array &$requestContext): ?Response
  {
    try {
      $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

      if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
        return Response::error('Authentication token required', 401);
      }

      $token = substr($authHeader, 7);

      // 1. Validate Token & Extract Payload
      $payload = $this->jwtHandler->verifyAndDecode($token);

      if (!$payload || !isset($payload['sub'])) {
        return Response::error('Invalid or expired token', 401);
      }

      // 2. Identify User
      $userId = (int)$payload['sub'];
      $user = $this->userRepository->findById($userId);

      if (!$user) {
        return Response::error('User not found', 401);
      }

      // 3. Check Account Status (Active & Verified)
      if ($user->is_disabled) {
        return Response::error('Account is disabled', 403);
      }

      if (!$user->is_verified) {
        return Response::error('Account not verified', 403);
      }

      // 4. Inject User Context
      // This allows controllers to access the current user via $requestContext['user']
      $requestContext['user'] = $user;

      return null; // Authentication successful

    } catch (Exception $e) {
      // In production, log the actual error $e->getMessage()
      return Response::error('Authentication failed', 401);
    }
  }
}
