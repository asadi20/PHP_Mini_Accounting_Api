<?php

namespace app\Middlewares;

use app\Repositories\UserRepository;
use app\Services\JwtService;
use core\Middleware;
use core\Request;
use core\Response;

class JwtAuthMiddleware implements Middleware
{
    private JwtService $jwtService;
    private UserRepository $userRepository;

    public function __construct(JwtService $jwtService, UserRepository $userRepository)
    {
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
    }

    public function handle(callable $next, Request $request): mixed
    {
        // in FPM/Nginx authHeader is store in $_SERVER['HTTP_AUTORIZATION'];
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($authHeader === null) {
            return Response::json(['message' => 'Authorization header missing'], 401);
        }

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return Response::json(['message' => 'Invalid Ahutorization format.'], 401);
        }

        // to get token from header;
        $jwt = $matches[1];
        try {
            $decodeToken = $this->jwtService->decode($jwt);

            $userId = $decodeToken['sub'] ?? null;

            if (!$userId) {
                return Response::json(['message' => 'user ID not found in token'], 401);
            }

            $user = $this->userRepository->findByUserId($userId);

            if (!$user) {
                return Response::json(['message' => 'user not found']);
            }
            // $request = new Request();
            $request->setAuthenticatedUserId($userId);
            // $this->authService->setAuthenticatedUser($user);
            return $next($request);
        } catch (\Exception $e) {
            return Response::json(
                [
                    'message' => 'Invalid or Expired token',
                    'error' => $e->getMessage()
                ], 401
            );
        }
    }
}
