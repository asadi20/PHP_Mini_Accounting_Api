<?php
namespace app\Repositories;

use PDO;

class RbacRepository implements RbacRepositoryInterface
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * find all roles that assigned to a specific user
     * @param int $userId
     * @return array|null
     */
    public function findRolesByUserId(int $userId): ?array
    {
        $qry = 'SELECT role_id, roles.name from role_user
        left join roles on roles.id=role_user.role_id 
        where role_user.user_id= :user_id';

        $stmt = $this->db->prepare($qry);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $roles = $stmt->fetchAll();
        return $roles;
    }

    /**
     * find all permissions assigned to specific user
     * @param int $userId
     * @return array|null
     */
    public function findPermissionsByUserId(int $userId): ?array
    {
        try {
            $qry = 'SELECT permission_role.role_id, roles.name as role_name , permissions.name as perm_name FROM permission_role
        LEFT JOIN roles ON roles.id = permission_role.role_id
        LEFT JOIN permissions ON permissions.id = permission_role.permission_id
        LEFT JOIN role_user ON role_user.role_id = permission_role.role_id
        WHERE role_user.user_id = :user_id';
            $stmt = $this->db->prepare($qry);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $perms = $stmt->fetchAll();
            return $perms;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public function findPermissionByUserId(int $userId, string $permissionName)
    {
        try {
            $qry = 'SELECT permission_role.role_id, roles.name as role_name , permissions.name as perm_name FROM permission_role
        LEFT JOIN roles ON roles.id = permission_role.role_id
        LEFT JOIN permissions ON permissions.id = permission_role.permission_id
        LEFT JOIN role_user ON role_user.role_id = permission_role.role_id
        WHERE role_user.user_id = :user_id AND permissions.name= :perm_name';
            $stmt = $this->db->prepare($qry);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':perm_name', $permissionName, PDO::PARAM_STR);
            $stmt->execute();
            $perms = $stmt->fetchAll();
            return $perms;
        } catch (\PDOException $e) {
            return $e->getmessage();
        }
    }

    public function findPermissionsByRoleId(int $roleId)
    {
        $qry = 'SELECT permission_role.permission_id, permissions.name FROM permission_role
        LEFT JOIN permissions ON permission_role.permission_id = permissions.id
         WHERE permission_role.role_id = :role_id';
        $stmt = $this->db->prepare($qry);
        $stmt->bindValue(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll();
        return $res;
    }

    /**
     * assign roles to user by id of both user and role
     * @param int $userId
     * @param array $rolesId
     * @return array|null of roles that assign to specific users
     */
    public function assignRolesToUser(int $userId, array $rolesId): ?int
    {
        // first we must repitive rows;
        foreach ($rolesId as $roleId) {
            $qry = 'INSERT IGNORE INTO role_user (user_id, role_id) VALUES (:user_id, :role_id)';
            // $stmt = $this->dbo->query($qry, [$userId, $roleId]);
            $stmt = $this->db->prepare($qry);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            try {
                $stmt->execute();
                $affectedRows = $stmt->rowCount();
                return $affectedRows;
            } catch (\PDOException $e) {
                // $logMessage = "[" . date("Y-m-d H:i:s") . "] PDOException: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
                // file_put_contents('/error.log', $logMessage, FILE_APPEND);
                echo 'Error has been occured, please contact your administrator.';
            }
        }
    }
}
