<?php

namespace app\Services;

use app\Repositories\RbacRepository;
use app\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;
    private RbacRepository $rbacRepository;

    public function __construct(UserRepository $userRepository, RbacRepository $rbacRepository)
    {
        $this->userRepository = $userRepository;
        $this->rbacRepository = $rbacRepository;
    }

    public function show(int $id)
    {
        $user = $this->userRepository->findByUserId($id);
        return $user;
    }

    public function getByUsername(string $username)
    {
        $user = $this->userRepository->findByUsername($username);
        return $user;
    }

    public function getRolesByUserId(int $userId)
    {
        $roles = $this->rbacRepository->findRolesbyUserId($userId);
        return $roles;
    }

    public function getPermissionsByUserId(int $userId): ?array
    {
        $perms = $this->rbacRepository->findPermissionsByUserId($userId);
        return $perms;
    }

    public function addNewUser(array $userData): ?int
    {
        $user = $this->userRepository->addNewUser($userData);
        return $user;
    }

    public function assignRolesToUser($userId, $rolesId): ?int
    {
        $roles = $this->rbacRepository->assignRolesToUser($userId, $rolesId);
        return $roles;
    }

    public function assignRolesToPermissions($rolesId, $permissionsId): ?int
    {
        $res = $this->rbacRepository->assignRolestoPermissions($rolesId, $permissionsId);
        return $res;
    }
}
