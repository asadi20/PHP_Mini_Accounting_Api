<?php
namespace app\Repositories;

use app\Models\UserModel;
use app\Repositories\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function findByUserId(int $id): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM users WHERE id= :id';
            $stmt = $this->db->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_CLASS, UserModel::class);
            $stmt->bindValue('id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\PDOException $e) {
            throw $e;
        }
    }
    /**
     * for find user data by username
     * @param $username
     * @return UserModel|null
     */
    public function findByUsername(string $username): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM users WHERE user_name= :user_name';
            $stmt = $this->db->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_CLASS, UserModel::class);
            $stmt->bindValue('user_name', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\PDOException $e) {
            throw $e;
        }
    }
    /**
     * for register a new user by user or by admin
     * @param $userData
     * @return string|null of last Insert ID or false
     */
    public function addNewUser(array $userData): string|null
    {
        try {
            $sql = 'INSERT INTO users (user_name, full_name, password, email) VALUES (:user_name, :full_name, :password, :email)';

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue('user_name', $userData['user_name'], PDO::PARAM_STR);
            $stmt->bindValue('full_name', $userData['full_name'], PDO::PARAM_STR);
            $stmt->bindValue('password', $userData['password'], PDO::PARAM_STR);
            $stmt->bindValue('email', $userData['email'], PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // PDO::lastInserId does not return integer format of Id of the last inserted row jsut retrun string !!
                // PDO::laseInsertId (int|false)
                return $this->db->lastInsertId();
            } else {
                return null;
            }
        } catch (\PDOException $e) {
            throw $e;
        }
    }
    public function findAllUsers(): array
    {
        $sql = 'SELECT * FROM users';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, UserModel::class);
        // fetchAll always return array even it's empty return empty array []
        return $stmt->fetchAll();
    }
    public function updateUserWithRoles(array $userData): array
    {
        try {
            $this->db->beginTransaction();
            $sql = 'SELECT id FROM users WHERE id= :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $userData['id'], PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                throw new \Exception('user not found.');
            }

            // update simple user information after fetching the user is existed.
            $this->updateUser($userData);

            // sync roles
            $id = $userData['id'];
            $roles = $userData['roles'];
            $this->syncRolesToUser($id, $roles);

            $this->db->commit();

            return ['success' => true, 'message' => 'update user information and sync roles.'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    /**
     * Summary of updateUser: simple user information update function
     * @param mixed $userData
     * @return int|null
     */
    private function updateUser($userData): string|null
    {
        $sql = 'UPDATE users SET user_name= :user_name, full_name= :full_name, email= :email, phone= :phone WHERE id = :id;';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_name', $userData['user_name'], PDO::PARAM_STR);
        $stmt->bindValue('full_name', $userData['full_name'], PDO::PARAM_STR);
        $stmt->bindValue('email', $userData['email'], PDO::PARAM_STR);
        $stmt->bindValue('phone', $userData['phone'], PDO::PARAM_STR);
        $stmt->bindValue('id', $userData['id'], PDO::PARAM_INT);

        try {
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->rowCount();
            } else {
                return null;
            }
        } catch (\PDOException $e) {
            throw $e;
        }
    }
    private function syncRolesToUser(int $userId, array $rolesId): ?int
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('user id not valid.');
        }

        // current Roles
        $stmt = $this->db->prepare("SELECT role_id FROM role_user WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $currentRoleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // detach
        $toDetach = array_diff($currentRoleIds, $rolesId);
        if (!empty($toDetach)) {
            $placeholders = str_repeat('?,', count($toDetach) - 1) . '?';
            $sql = "DELETE FROM role_user WHERE user_id = ? AND role_id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge([$userId], $toDetach));
        }

        // attach
        $toAttach = array_diff($rolesId, $currentRoleIds);
        if (!empty($toAttach)) {
            $values = [];
            $params = [];
            foreach ($toAttach as $roleId) {
                $values[] = "(?, ?)";
                $params[] = $userId;
                $params[] = $roleId;
            }
            $sql = "INSERT IGNORE INTO role_user (user_id, role_id) VALUES " . implode(',', $values);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

        return \count($toDetach) + \count($toAttach);
    }
}
