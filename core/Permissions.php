<?php
return [
    // users
    'GET /users' => 'users.read',
    'POST /users' => 'users.create',
    'PUT /users' => 'users.update',
    'DELETE /users' => 'users.delete',
    
    //roles
    'GET /roles' => 'roles.read',
    'POST /roles' => 'roles.create',
    'PUT /roles' => 'roles.update',
    'DELETE /roles' => 'roles.delete'
];