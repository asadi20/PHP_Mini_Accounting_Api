<?php
namespace app\Repositories;

use app\Models\RoleModel;
use app\Repositories\RoleRepositoryInterface;
use PDO;

class RoleRepository implements RoleRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAllRoles(): array
    {
        $sql = 'SELECT * FROM roles';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, RoleModel::class);
        return $stmt->fetchAll();
    }

    public function findRoleById($roleId)
    {
        try {
            $sql = 'SELECT * FROM roles WHERE id= :id';
            $stmt = $this->db->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_CLASS, RoleModel::class);
            $stmt->bindValue('id', $roleId, PDO::PARAM_INT);
            $stmt->execute();
            $role = $stmt->fetch();
            return $role ?: null;
        } catch (\PDOException $e) {
            throw $e;
        }
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
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':desc', $description, PDO::PARAM_STR);
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

    public function updateRole(int $id, string $name, ?string $description = null): int
    {
        $sql = 'UPDATE roles SET name = :name, description = :description WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function updateRoleWithPermissions(int $id, string $name, ?string $description, array $permIds = []): array
    {
        try {
            $this->db->beginTransaction();

            // existence req role
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new \Exception('نقش یافت نشد');
            }

            // update role
            $this->updateRole($id, $name, $description);
            // sync permissions
            $this->assignPermissionsToRole($id, $permIds);

            $this->db->commit();

            return ['success' => true, 'message' => 'نقش و پرمیشن‌ها با موفقیت به‌روزرسانی شد'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function assignPermissionsToRole(int $roleId, array $permissionsId): int
    {
        if ($roleId <= 0) {
            throw new \InvalidArgumentException('شناسه نقش معتبر نیست');
        }

        // current permissions
        $stmt = $this->db->prepare("SELECT permission_id FROM permission_role WHERE role_id = :role_id");
        $stmt->bindValue(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        $currentPermissionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // detach
        $toDetach = array_diff($currentPermissionIds, $permissionsId);
        if (!empty($toDetach)) {
            $placeholders = str_repeat('?,', count($toDetach) - 1) . '?';
            $sql = "DELETE FROM permission_role WHERE role_id = ? AND permission_id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge([$roleId], $toDetach));
        }

        // attach
        $toAttach = array_diff($permissionsId, $currentPermissionIds);
        if (!empty($toAttach)) {
            $values = [];
            $params = [];
            foreach ($toAttach as $permId) {
                $values[] = "(?, ?)";
                $params[] = $roleId;
                $params[] = $permId;
            }
            $sql = "INSERT IGNORE INTO permission_role (role_id, permission_id) VALUES " . implode(',', $values);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

        return \count($toDetach) + \count($toAttach);
    }
}
