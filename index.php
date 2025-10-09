<?php
ini_set('display_errors',1);

use core\Router;

spl_autoload_register(function($className){
    // fix linux addressing system
    $className = str_replace("\\","/", $className);
    require $className.'.php';
});

$baseDir = "back/api/v1";
// remove slash from last character if we have one.
$request_uri = explode('/back/api/v1/',rtrim($_SERVER['REQUEST_URI'],'/'));
$request_method = $_SERVER['REQUEST_METHOD'];

if(!count($request_uri)==2){
    echo json_encode('path template Error!');
    return;
}

$router = new Router();
$router->matchRoute($request_uri[1], $request_method);