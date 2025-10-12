<?php

namespace app\Repositories;

use app\Models\RoleModel;

interface RoleRepositoryInterface
{
    public function findRolesByUserId(int $userId): ?RoleModel;
    public function addRole($name, $description): ?int;
    public function updateRole($id, $name, $description): ?int;
    public function deleteRole($id): ?int;
}
