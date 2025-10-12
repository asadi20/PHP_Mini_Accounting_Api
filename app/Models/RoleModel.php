<?php
namespace app\Models;

class RoleModel extends BaseModel
{
    public int $id;
    public string $name;
    public ?string $description;
}
