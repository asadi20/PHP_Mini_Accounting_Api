<?php

namespace app\Repositories;

use app\Models\PermissionModel;
use PDO;

class PermissionRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function addPermission($name, $desc)
    {
        $sql = 'INSERT INTO permissions (name, description) VALUES (:name, :desc);';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
        $rows = $stmt->execute();
        return $rows;
    }

    public function updatePermission($id, $name, $desc)
    {
        $sql = 'UPDATE permissions SET name= :name, description= :desc WHERE id= :id;';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
        $res = $stmt->execute();

        return $res;

    }

    public function findAllPermissions(): array
    {
        $sql = 'SELECT * FROM permissions';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, PermissionModel::class);
        return $stmt->fetchAll();
    }

    public function findPermissionById($id)
    {
        $sql = 'SELECT * FROM permissions WHERE id= :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id',$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
}
