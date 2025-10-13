<?php

namespace app\Services;

use app\Models\UserModel;
use app\Services\AuthorizationService;
use app\Services\JwtService;
use app\Services\UserService;
use core\Response;

class AuthService
{
    private UserService $userService;
    private JwtService $jwtService;
    private ?UserModel $authenticatedUser = null;

    public function __construct(UserService $userService, JwtService $jwtService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    public function setAuthenticatedUser(?UserModel $user): void
    {
        $this->authenticatedUser = $user;
    }

    public function user(): ?UserModel
    {
        return $this->authenticatedUser;
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticatedUser !== null;
    }

    public function attemptlogin(string $username, string $password): string
    {
        $user = $this->userService->getByUsername($username);

        if (!$user || !$user->verifyPassword($password)) {
            http_response_code(401);
            return json_encode(['error' => 'user not found or password is incorrect']);
        }

        $jwt = $this->jwtService->encode($user);

        $roles = $this->userService->getRolesByUserId($user->id);
        $perms = $this->userService->getPermissionsByUserId($user->id);

        $res = [
            'success' => true,
            'message' => 'login is successfull',
            'data' => [
                'user_name' => $user->user_name,
                'full_name' => $user->full_name,
                'token' => $jwt,
                'roles' => $roles,
                'perms' => $perms
            ],
            'errors' => null,
            'code' => 'OK_200'
        ];
        return json_encode($res);
    }

    public function registerNewUser(array $userData)
    {
        $userDup = $this->userService->getByUsername($userData['user_name']);
        if ($userDup) {
            return Response::json(['error' => 'duplicate user!'], 200);
        }

        // hash plain password
        $hashed_pwd = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['password'] = $hashed_pwd;
        $newUser = $this->userService->addNewUser($userData);
        if ($newUser === null) {
            throw new \Exception('error has been occured');
        }
        return $newUser;
    }
}
