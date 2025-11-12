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

    public function attemptlogin(string $username, string $password): array|string|false
    {
        $user = $this->userService->getByUsername($username);

        if (!$user || !$user->verifyPassword($password)) {
            $res = [
                'success' => false,
                'message' => 'user not found or password is incorrect',
                'data' => [],
                'errors'=> null,
                'code'=>'401'
            ];

            return $res;
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
            'code' => '200'
        ];
        return $res;
    }

    public function registerNewUser(array $userData)
    {
        $userDup = $this->userService->getByUsername($userData['user_name']);
        if ($userDup) {
            throw new \Exception('Duplicate user');
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

    public function validateToken(string $token): bool
    {
        return $this->jwtService->decode($token);
    }
}
