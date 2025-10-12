<?php

namespace app\Repositories;

use app\Models\RoleModel;
use app\Repository\RoleRepositoryInterface;
use PDO;

class RoleRepository implements RoleRepositoryInterface
{
    private PDO $dbo;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * find all roles assign to specific user.
     * @param int $userId
     * @return array<RoleModel>
     */
    public function findRolesByUserId($userId): ?RoleModel
    {
        $sql = 'SELECT * FROM role_user where user_id= :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $roles = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $role = new RoleModel();
            $role->id = $row['id'];
            $role->name = $row['name'];
            $role->description = $row['description'];
            $roles[] = $role;
        }

        return $roles ?: null;
    }

    public function addRole($name, $description): int
    {
        $qry = 'INSERT INTO roles (name, description) VALUES (:name, :desc);';
        try {
            $stmt = $this->db->prepare($qry);
            $stmt->binParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':desc', $description, PDO::PARAM_STR);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $e;
        }
        return $affectedRows;
    }

    public function updateRole($id, $name, $description): ?int
    {
        $qry = 'UPDATE roles SET (name, description) VALUES (:name, :desc) WHERE id= :id;';
        try {
            $stmt = $this->db->prepare($qry);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':desc', $description, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $e;
        }
        return $affectedRows;
    }

    public function deleteRole($id): ?int
    {
        try {
            $qry = "DELETE FROM roles WHERE id= :id";
            $stmt = $this->db->prepare($qry);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $e;
        }
        return $affectedRows;
    }
}
