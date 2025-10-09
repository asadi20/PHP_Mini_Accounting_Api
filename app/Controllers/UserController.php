<?php
namespace app\Controller;

class UserController
{
    public function index()
    {
        echo json_encode('UserController');
        return;
    }

    public function show($id)
    {
        echo json_encode($id);
        return;
    }
}