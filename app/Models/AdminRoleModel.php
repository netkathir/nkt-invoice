<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminRoleModel extends Model
{
    protected $table            = 'admin_roles';
    protected $primaryKey       = '';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = ['admin_id', 'role_id'];

    public function forAdmin(int $adminId): array
    {
        return $this->where('admin_id', $adminId)->findAll();
    }
}

