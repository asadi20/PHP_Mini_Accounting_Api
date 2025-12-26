<?php

namespace app\Repositories;

Interface RbacRepositoryInterface
{
    public function findRolesByUserId(int $userId): ?array;
    // find Permissions
    public function findPermissionsByUserId(int $userId): ?array;
    // find Permission
    public function findPermissionByUserId(int $userId, string $permissionName);
    public function findPermissionsByRoleId(int $roleId);
    public function assignRolesToUser(int $userId, array $rolesId): ?int;
}
