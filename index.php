<?php
ini_set('display_errors', 1);

use core\Router;
use core\Container;

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

spl_autoload_register(function ($className) {
    // fix linux addressing system
    $className = str_replace('\\', '/', $className);
    require $className . '.php';
});

$basePath = '/back/api/v1';
// Remove trailing slash from requested uri
$request_uri = rtrim($_SERVER['REQUEST_URI'], '/');
if (strpos($request_uri, $basePath) === 0) {
    $request_uri = substr($request_uri, strlen($basePath));
} else {
    throw new \InvalidArgumentException("Invalid base path in URI: URI does not start with expected prefix $basePath.");
}

$request_method = $_SERVER['REQUEST_METHOD'];
$container = new Container();
$router = new Router($container);
$router->matchRoute($request_uri, $request_method);
