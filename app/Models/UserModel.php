<?php
namespace app\Models;

class UserModel extends BaseModel
{
    public int $id;
    public string $user_name;
    public string $full_name;
    public ?string $email;
    public ?string $email_verified;
    public ?string $email_verified_at; //Nullable
    public ?string $created_at; //Nullable
    public ?string $updated_at; // Nullable
    private ?string $remember_token; //Nullable
    private string $password;

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}

