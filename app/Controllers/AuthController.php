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
            return Response::json(['success'=>"user created with id: $res"], 201);
        } catch(\Exception $e){
            if($e->getMessage()==='Duplicate user'){
                return Response::json(['error'=>'username that you want already exist'], 200);
            }
            return Response::json(['error'=>"Registration failed {$e->getMessage()}"],500);
        }
    }

    public function login(Request $req)
    {
        $username = $req->input('username');
        $pass = $req->input('password');

        if (empty($username) || empty($pass)) {
            echo json_encode(['error' => 'user name or password field empty']);
            return;
        }
        $res = $this->authService->attemptLogin($username, $pass);
        echo $res;
        return;
    }
}
