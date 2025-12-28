<?php

namespace app\Controllers;

use app\Services\AuthorizationService;
use app\Services\AuthService;
use app\Services\UserService;
use core\Request;
use core\Response;

class UserController
{
    private AuthService $authService;
    private UserService $userService;
    private AuthorizationService $authorizationService;

    public function __construct(AuthService $authService, UserService $userService, AuthorizationService $authorizationService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->authorizationService = $authorizationService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return Response::json($users, 200);
    }

    public function show(Request $request)
    {
        $user_requested = $this->userService->show($request->routeParam('id'));
        if (!$user_requested) {
            return Response::json(['message' => 'user not found'], 404);
        }
        return Response::json($user_requested, 200);
    }

    /**
     * Summary of updateUserWithRoles: update user information with assign roles to the specific user
     * @param Request $req
     * @return void
     */
    public function updateUserWithRoles(Request $req): void
    {
        // roles in array that store roles related to the user
        $userData = [
            'id' => $req->routeParam('id'),
            'user_name' => $req->input('user_name'),
            'full_name' => $req->input('full_name'),
            'email' => $req->input('email'),
            'phone' => $req->input('phone'),
            'roles' => $req->input('roles')
        ];

        $res = $this->userService->updateUserWithRoles($userData);

        $message = [
            'success'=>true,
            'data' => $res,
            'message' => 'update_successful',
            'error' => ''
        ];

        Response::json($message, 200);
    }

    public function assignPermissionsToRole(Request $req): ?array
    {
        $roleId = $req->routeParam('id');
        //$data = $req->all(); // یا validation

        // آپدیت نام و توضیح نقش
        //$this->roleRepository->updateRole($roleId, $data['name'], $data['description'] ?? null);

        // sync پرمیشن‌ها
        $permissions = $req->input('permissions') ?? []; // آرایه id پرمیشن‌ها از فرم
        $affected = $this->userService->assignPermissionsToRole($roleId, $permissions);

        return Response::json([
            'success' => true,
            'message' => 'نقش و پرمیشن‌ها با موفقیت ذخیره شد',
            'data' => ['affected_permissions' => $affected]
        ], 200);
    }

    public function addNewUser(Request $request): ?int
    {
        $hashed_pwd = password_hash($request->input('password'), PASSWORD_DEFAULT);

        $userData = [
            'user_name' => $request->input('username'),
            'full_name' => $request->input('fullname'),
            'password' => $hashed_pwd,
            'email' => $request->input('email')
        ];

        $res = $this->userService->addNewUser($userData);

        return $res;
    }
}
