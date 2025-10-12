<?php

namespace app\Repositories;

Interface RbacRepositoryInterface
{
    public function findRolesByUserId(int $userId): ?array;
    public function findPermissionsByUserId(int $userId): ?array;
    public function assignRolesToUser(int $userId, array $rolesId): ?int;
    public function assignRolesToPermissions(array $roleId, array $permissionsId): ?int;
}
