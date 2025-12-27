<?php

namespace app\Middlewares;

use app\Services\RbacService;
use core\Request;
use core\Response;

class RbacMiddleware
{
    private array $permissions;
    private RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->permissions = include_once __DIR__ . '/../../core/Permissions.php';
        $this->rbacService = $rbacService;
    }

    public function handle(callable $next, Request $request)
    {
        // url structure like 'GET /users'
        $path = $this->getCleanPath($request->getUri());
        $key = $request->getMethod() . ' ' . $path;
        $required = $this->permissions[$key] ?? null;

        if ($required) {
            $userId = $request->getAuthenticatedUserId();
            if (!$this->rbacService->hasPermission($userId, $required)) {
                return Response::json(['message' => 'Forbidden'], 403);
            }
        }
        return $next($request);
    }

    public function getCleanPath(string $fullPath): string
    {
        $config = require_once __DIR__ . '/../../core/Config.php';
        $basePath = BASE_PATH ;

        if (str_starts_with($fullPath, $basePath)) {
            return substr($fullPath, \strlen($basePath)) ?: '/';
        }

        return $fullPath;
    }
}