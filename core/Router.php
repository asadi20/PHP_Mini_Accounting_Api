<?php
namespace core;

use app\Controllers\HomeController;
use app\Controllers\UserController;

class Router
{
    public function __construct()
    {
        $this->addRoute(['','GET', HomeController::class, 'index']);
        $this->addRoute(['/users', 'GET', UserController::class, 'index']);
        $this->addRoute(['/user/{id}', 'GET', UserController::class, 'show']);
    }

    public function addRoute($route)
    {
        var_dump($route);
    }

    public function matchRoute($route, $method)
    {
        echo json_encode($method);
    }
}