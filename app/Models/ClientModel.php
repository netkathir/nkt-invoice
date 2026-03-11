<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';

    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = ['name', 'contact_person', 'email', 'phone', 'status'];

    protected $validationRules = [
        'name'           => 'permit_empty|max_length[191]',
        'contact_person' => 'required|max_length[191]',
        'email'          => 'permit_empty|valid_email|max_length[191]',
        'phone'          => 'permit_empty|max_length[50]',
        'status'         => 'permit_empty|in_list[' . self::STATUS_ACTIVE . ',' . self::STATUS_INACTIVE . ']',
    ];
}
