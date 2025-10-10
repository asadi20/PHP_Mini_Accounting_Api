<?php
ini_set('display_errors', 1);

use core\Router;

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

$router = new Router();
$router->matchRoute($request_uri, $request_method);
