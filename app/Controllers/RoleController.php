<?php

namespace app\Controllers;

use app\Repositories\RoleRepository;
use app\Services\RbacService;
use core\Request;
use core\Response;


class RoleController
{
    private RbacService $rbacService;
    private RoleRepository $roleRepository;

    public function __construct(RbacService $rbacService, RoleRepository $roleRepository)
    {
        $this->rbacService = $rbacService;
        $this->roleRepository = $roleRepository;
    }
    public function index()
    {
        $roles = $this->roleRepository->findAllRoles();
        return Response::json($roles, 200);
    }

    public function show(Request $req)
    {
        $roleId = $req->routeParam('id');
        $role = $this->roleRepository->findRoleById($roleId);
        return Response::json($role, 200);

    }

    public function update(Request $req)
    {
        $roleId = $req->routeParam('id');
        $name = $req->input('name');
        $desc = $req->input('description');

        try {
            $role = $this->roleRepository->updateRole($roleId, $name, $desc);
            if ($role === 0) {
                return Response::json([
                    'success' => false,
                    'message' => 'هیچ تغییری اعمال نشد یا نقش یافت نشد'
                ], 404);
            }

            return Response::json([
                'success' => true,
                'message' => 'نقش با موفقیت به‌روزرسانی شد',
                'data' => [
                    'affected_rows' => $role,
                    'role_id' => $roleId
                ]
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'خطا در سرور'
            ], 500);
        }
    }

    public function addRole(Request $req)
    {
        $name = $req->input('name');
        $description = $req->input('description');
        $role = $this->roleRepository->addRole($name, $description);
        return Response::json(['message' => 'number of affected rows' . $role], 200);
    }
    public function getRolesByUserId(Request $request): array|null
    {
        $uid = $request->routeParam('id');
        $roles = $this->rbacService->getRolesByUserId($uid);
        return Response::json($roles, 200);
    }
}