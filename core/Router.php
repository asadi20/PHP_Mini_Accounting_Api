<?php
namespace Core;

use App\Controllers\UserController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use Core\Container;
use Core\Request;
use Core\Middleware;
use App\Middlewares\JwtAuthMiddleware;
use Exception;

class Router
{
    private $controller_name;
    private $function_name;
    private Container $container; // add container
    private $routes = [];

    public function __construct(Container $container)
    {
        $baseDir = '/back/api/v1/';
        $this->container = $container;
        
        $this->addRoute($baseDir.'users', 'GET', UserController::class, 'index');
        $this->addRoute($baseDir.'user/(\d+)', 'GET', UserController::class, 'show',[JwtAuthMiddleware::class]);
        $this->addRoute($baseDir.'user/addNewUser','POST', UserController::class,'addNewUser',[JwtAuthMiddleware::class]);
        $this->addRoute($baseDir.'', 'GET', HomeController::class, 'index');
        $this->addRoute($baseDir.'login', 'POST', AuthController::class, 'login');
        $this->addRoute($baseDir.'register', 'POST', AuthController::class, 'register');
        $this->addRoute($baseDir.'assign_roles','POST',UserController::class,'assignRolesToUser',[JwtAuthMiddleware::class]);
        $this->addRoute($baseDir.'assign_roles_perms', 'POST', UserController::class, 'assignRolesToPermissions', [JwtAuthMiddleware::class]);
    }

    public function addRoute(string $path,string $method,
    string $controller_class,string $action_method,array $middlewares = [])
    {
        $this->routes [] =
        [
            'path' => $path,
            'method'=>$method,
            'controller'=>$controller_class,
            'action'=>$action_method,
            'middlewares'=>$middlewares
        ];
    }

    public function matchRoute(string $req_uri, string $method)
    {
        $request = new Request();
        
        foreach ($this->routes as $route) {
            // The preg_match will now correctly handle the optional trailing slash
            if (preg_match('#^' . $route['path'] . '$#', $req_uri, $matches) && $method === $route['method']) {
                $this->controller_name = $route['controller'];
                $this->function_name = $route['action'];
                
                $finalCallable = function () use ($request, $matches) {
                    $controllerInstance = $this->container->buildInstance($this->controller_name);
                    // get id from url and store it to params array;
                    $params = [];
                    // The ID is captured in $matches[1] due to (\d+)
                    $params['id'] = isset($matches[1]) ? (int)$matches[1] : null;
                    $request->setRouteParams($params);
                    if (method_exists($controllerInstance, $this->function_name)) {
                    $controllerInstance->{$this->function_name}($request);
                    return; // Route matched and executed, so exit.
                    } else {
                        // Handle case where method does not exist (shouldn't happen with correct routing)
                        header("HTTP/1.0 500 Internal Server Error");
                        echo json_encode(['error' => 'Controller method not found.']);
                    }
                };

                // Execute middlewares if any
                if (!empty($route['middlewares'])) {
                    $this->executeMiddlewares($route['middlewares'], $finalCallable, $request);
                } else {
                    // No middlewares, just execute the final callable
                    try{
                        $finalCallable();
                    } catch(Exception $e)
                    {
                        echo $e->getMessage();
                    }
                
                }
                return; // Route matched and executed, so exit.
            }
        }
        // If no route matches, return a 404 Not Found response
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error'=>'Path Not Found']);
    }

    private function executeMiddlewares(array $middlewares, callable $finalCallback,Request $request)
    {
        $chain = $finalCallback;
        // Build the middleware chain in reverse order;
        foreach (array_reverse($middlewares) as $middlewareClass)
        {
            $middlewareInstance = $this->container->buildInstance($middlewareClass);

            $chain = function () use ($middlewareInstance, $chain, $request){
                return $middlewareInstance->handle($chain, $request);
            };
        }
        // Execute first middleware ( cuase dominate other middleware run);
        $chain();
    }
}