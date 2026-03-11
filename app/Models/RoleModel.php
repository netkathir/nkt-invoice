<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['name', 'description', 'is_super'];

    protected $validationRules = [
        'name'        => 'required|max_length[191]',
        'description' => 'permit_empty',
        'is_super'    => 'permit_empty|in_list[0,1]',
    ];
}

