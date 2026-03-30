<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyInformationModel extends Model
{
    protected $table          = 'company_information';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $dateFormat     = 'datetime';
    protected $allowedFields  = [
        'company_name',
        'logo_path',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'pincode',
        'gstin_number',
        'export_tax_reference',
        'email_id',
        'website',
        'phone_number',
        'current_account_details',
        'paypal_account',
    ];

    protected $validationRules = [
        'company_name' => 'required|max_length[191]',
        'logo_path' => 'permit_empty|max_length[255]',
        'address_line1' => 'required|max_length[255]',
        'address_line2' => 'permit_empty|max_length[255]',
        'city' => 'required|max_length[100]',
        'state' => 'required|max_length[100]',
        'pincode' => 'required|regex_match[/^[0-9]{6}$/]',
        'gstin_number' => 'required|regex_match[/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/]',
        'export_tax_reference' => 'permit_empty|max_length[191]',
        'email_id' => 'permit_empty|valid_email|max_length[191]',
        'website' => 'permit_empty|max_length[191]',
        'phone_number' => 'permit_empty|max_length[20]',
        'current_account_details' => 'permit_empty|max_length[255]',
        'paypal_account' => 'permit_empty|valid_email|max_length[191]',
    ];

    protected $validationMessages = [
        'gstin_number' => [
            'required' => 'GSTIN Number is required.',
            'regex_match' => '15 characters: 2 digits + 5 letters + 4 digits + 1 letter + 1 alphanumeric + Z + 1 alphanumeric',
        ],
    ];
}
