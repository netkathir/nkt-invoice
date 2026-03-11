<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['key', 'label', 'module', 'description'];

    protected $validationRules = [
        'key'         => 'required|max_length[191]',
        'label'       => 'required|max_length[191]',
        'module'      => 'permit_empty|max_length[100]',
        'description' => 'permit_empty',
    ];
}

