<?php

namespace app\Repositories;

use app\Models\UserModel;

interface UserRepositoryInterface
{
    public function findByUserId(int $id);
    public function findByUsername(string $username): ?UserModel;
    public function addNewUser(array $userData): string|null;
}
