<?php

namespace app\Controllers;

use app\Models\UserModel;
use app\Services\AuthService;
use app\Services\UserService;
use core\Request;
use core\Response;

class AuthController
{
    private AuthService $authService;
    private UserService $userService;

    public function __construct(AuthService $authService, UserService $userService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
    }

    public function register(Request $req)
    {
        $userData = [
            'user_name' => $req->input('username'),
            'full_name' => $req->input('fullName'),
            'email' => $req->input('email'),
            'password' => $req->input('password')
        ];
        // validate input fields.
        try {
            $res = $this->authService->registerNewUser($userData);
            return Response::json(['success' => "user created with id: $res"], 201);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Duplicate user') {
                return Response::json(['error' => 'username that you want already exist'], 200);
            }
            return Response::json(['error' => "Registration failed {$e->getMessage()}"], 500);
        }
    }

    public function login(Request $req)
    {
        $userData = [
            'username' => $req->input('username'),
            'password' => $req->input('password')
        ];

        if (empty($userData['username']) || empty($userData['password'])) {
            echo json_encode(['error' => 'user name or password field empty']);
            return;
        }
        $res = $this->authService->attemptLogin($userData['username'], $userData['password']);
        if ($res['success'] && isset($res['data']['token'])) {
            setcookie('token', $res['data']['token'], [
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Strict',
                'path' => '/',
                'expires' => time() + 3600
            ]);
            unset($res['data']['token']);
        }
        return Response::json($res, $res['code']);
    }

    public function checkToken(Request $req): array
    {
        $token = $req->input('token');

        if (!$token) {
            $data = [
                'success'=> false,
                'message'=> 'Authentication token missing or invalid.',
                'data'=> '',
                'errors'=> null,
                'code'=>200
            ];
            return Response::json($data, $data['code']);
        }
        $decoded = $this->authService->validateToken($token);
        if (is_array($decoded) && !empty($decoded)) {
            $data = [
                'success'=> true,
                'message'=> 'toekn is valid.',
                'data'=> $decoded,
                'errors'=> null,
                'code'=>200
            ];
            return Response::json($data, $data['code']);
        }
        $data = [
                'success'=> false,
                'message'=> 'toekn is not valid.',
                'data'=> '',
                'errors'=> null,
                'code'=>200
            ];
            return Response::json($data, $data['code']);
    }
}
