<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admins';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['name', 'email', 'username', 'password'];

    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[191]',
        'email'    => 'required|valid_email|max_length[191]',
        'username' => 'required|min_length[3]|max_length[50]',
        'password' => 'required|min_length[8]|max_length[255]',
    ];
}

