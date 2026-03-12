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
    protected $allowedFields    = [
        'client_code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'billing_address',
        'city',
        'state',
        'country',
        'postal_code',
        'status',
    ];

    protected $validationRules = [
        'client_code'     => 'permit_empty|max_length[50]',
        'name'            => 'permit_empty|max_length[191]',
        'contact_person'  => 'required|max_length[191]',
        'email'           => 'permit_empty|valid_email|max_length[191]',
        'phone'           => 'permit_empty|max_length[50]',
        'address'         => 'permit_empty|max_length[2000]',
        'billing_address' => 'permit_empty|max_length[2000]',
        'city'            => 'permit_empty|max_length[100]',
        'state'           => 'permit_empty|max_length[100]',
        'country'         => 'permit_empty|max_length[100]',
        'postal_code'     => 'permit_empty|max_length[20]',
        'status'          => 'permit_empty|in_list[' . self::STATUS_ACTIVE . ',' . self::STATUS_INACTIVE . ']',
    ];
}
