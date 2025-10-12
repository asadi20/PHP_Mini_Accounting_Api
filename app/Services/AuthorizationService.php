<?php

namespace app\Services;

use app\Models\UserModel;
use app\Repositories\RbacRepository;
use app\Services\AuthService;

class AuthorizationService
{
    private AuthService $authService;
    private RbacRepository $rbacRepository;

    public function __construct(AuthService $authService, RbacRepository $rbacRepository)
    {
        $this->authService = $authService;
        $this->rbacRepository = $rbacRepository;
    }

    public function check(): bool
    {
        return $this->authService->user() !== null;
    }

    public function user(): ?User
    {
        return $this->authService->user();
    }

    public function can(string $ability, ...$arguments): bool
    {
        $user = $this->authService->user();
        if (!$this->check()) {
            // user not logged in
            return false;
        }
        $policyClass = null;
        $policyArguments = [];

        if (!empty($arguments) && $arguments[0] instanceof User) {
            $policyClass = UserPolicy::class;
            $policyArguments = $arguments;
        }

        if ($policyClass) {
            $policy = $this->container->get($policyClass);
            if (method_exists($policy, $ability)) {
                return $policy->$ability(...$policyArguments);
            }
        }
        return False;
    }

    public function konnen(string $ability, $userId)
    {
        $perm = $this->rbacRepository->findPermissionByUserID($userId, $ability);
        if (!$perm) {
            return false;
        }
        return true;
    }
}
