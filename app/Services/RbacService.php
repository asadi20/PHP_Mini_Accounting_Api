<?php
namespace app\Services;

use app\Repositories\RbacRepository;

class RbacService
{
    private RoleRepository $roleRepository;
    private PermissionRepository $permissionRepository;
    private RbacRepository $rbacRepository;

    public function __construct(RoleRepository $roleRepository,
        PermissionRepository $permissionRepository,
        RbacRepository $rbacRepository)
    {
        $this->RoleRepository = $roleRepository;
        $this->PermissionRepository = $permissionRepository;
        $this->rbacrepository = $rbacRepository;
    }

    public function getRoleByUserName(string $userName)
    {
        return $userName;
    }

    public function getRolesByUserId(int $userId)
    {
        $roles = $this->rbacRepository->findRolesByUserId($userId);
    }

    /**
     * get Permissions that assign specific Roles
     * @param int $roleId
     * @return array $permissions
     */
    public function getPermissionByRoleId(int $roleId): array
    {
        return $roleId;
    }
}
