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

    public function index(Request $request)
    {
        // TODO: show list of all users to the logined user
        var_dump($request->getAuthenticatedUserId());
    }

    public function show(Request $request)
    {
        // first we must get user id of token owner;
        // then with user id hided in token we pass it down in authorizationService->konnen
        $userId = $request->getAuthenticatedUserId();
        $kon = $this->authorizationService->konnen('user-view', $userId);
        if (!$kon) {
            // 401 for unauthorized, 403 for forbidden;
            return Response::json(['message' => 'You do not have access to show the requested user information'], 401);
        }
        $user_requested = $this->userService->show($request->routeParam('id'));
        if (!$user_requested) {
            return Response::json(['message' => 'user not found'], 404);
        }
        return Response::json(array($user_requested), 200);
    }

    public function assignRolesToUser(Request $req): ?int
    {
        $userId = $req->input('userId');
        $rolesId = $req->input('roleId');
        $roles = $this->userService->assignRolesToUser($userId, $rolesId);
        return Response::json(['message' => $roles . ' row(s) affected.']);
    }

    public function assignRolesToPermissions(Request $req): ?array
    {
        $rolesId = $req->input('roleId');
        $permissionsId = $req->input('permissionId');
        $res = $this->userService->assignRolesToPermissions($rolesId, $permissionsId);
        return Response::json(['message' => $res . ' row(s) affected.']);
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
