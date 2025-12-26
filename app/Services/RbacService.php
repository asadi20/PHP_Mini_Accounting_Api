<?php
namespace app\Services;

use app\Repositories\RbacRepository;

class RbacService
{
    private RbacRepository $rbacRepository;

    public function __construct(RbacRepository $rbacRepository)
    {
        $this->rbacRepository = $rbacRepository;
    }

    public function getRoleByUserName(string $userName)
    {
        return $userName;
    }

    public function getRolesByUserId(int $userId): array|null
    {
        $roles = $this->rbacRepository->findRolesByUserId($userId);
        return $roles;
    }

    /**
     * get Permissions that assign specific Roles
     * @param int $roleId
     * @return array $permissions
     */
    public function getPermissionsByRoleId(int $roleId): array
    {
        $perms = $this->rbacRepository->findPermissionsByRoleId($roleId);
        return $perms;
    }
}
