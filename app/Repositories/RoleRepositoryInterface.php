<?php

namespace app\Repositories;

use app\Models\RoleModel;

interface RoleRepositoryInterface
{
    public function findAllRoles(): array;
    public function findRolesByUserId(int $userId): ?RoleModel;
    public function addRole($name, $description): ?int;
    public function updateRole(int $id,string $name, ?string $description): ?int;
    public function updateRoleWithPermissions(int $id, string $name, ?string $description, array $permIds = []): array;
    public function deleteRole($id): ?int;
}
