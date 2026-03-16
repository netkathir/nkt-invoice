<?php

namespace App\Models;

use CodeIgniter\Model;

class ProformaPaymentModel extends Model
{
    protected $table            = 'proforma_payments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = [
        'proforma_id',
        'client_id',
        'payment_date',
        'payment_mode',
        'amount',
        'reference_number',
        'remarks',
    ];

    protected $validationRules = [
        'proforma_id'       => 'required|is_natural_no_zero',
        'client_id'         => 'required|is_natural_no_zero',
        'payment_date'      => 'required|valid_date[Y-m-d]',
        'payment_mode'      => 'permit_empty|max_length[50]',
        'amount'            => 'required|decimal|greater_than[0]',
        'reference_number'  => 'permit_empty|max_length[100]',
        'remarks'           => 'permit_empty|max_length[5000]',
    ];
}

