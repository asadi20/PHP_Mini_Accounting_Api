<?php

namespace app\Controllers;
use app\Repositories\PermissionRepository;
use app\Services\RbacService;
use core\Request;
use core\Response;

class PermissionController
{
    private PermissionRepository $permissionRepository;
    private RbacService $rbacService;
    public function __construct(PermissionRepository $permissionRepository, RbacService $rbacService)
    {
        $this->permissionRepository = $permissionRepository;
        $this->rbacService = $rbacService;
    }

    public function index()
    {
        $perms = $this->permissionRepository->findAllPermissions();
        return Response::json($perms,200);
    }

    public function show(Request $req)
    {
        $id = $req->routeParam('id');
        $perm = $this->permissionRepository->findPermissionById($id);
        return Response::json($perm, 200);
    }

    public function addPermission(Request $req)
    {
        $name = $req->input('name');
        $desc = $req->input('description');

        $res = $this->permissionRepository->addPermission($name, $desc);
        return Response::json(['message'=>'success', 'data'=>$res], 200);
    }

    public function updatePermission(Request $req)
    {
        $name = $req->input('name');
        $desc = $req->input('description');
        $id = $req->routeParam('id');

        $res = $this->permissionRepository->updatePermission($id, $name, $desc);

        return Response::json(['message'=>'success', 'data'=>$res], 200);
    }

    public function getPermsByRoleId(Request $req)
    {
        $roleId = $req->routeParam('id');
        $perms = $this->rbacService->getPermissionsByRoleId($roleId);
        return Response::json($perms, 200);
    }
}