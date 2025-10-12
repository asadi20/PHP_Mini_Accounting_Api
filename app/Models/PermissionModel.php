<?php

namespace app\Models;

class PermissionModel extends BaseModel
{
    public int $id;
    public string $name;
    public ?string $description;
}