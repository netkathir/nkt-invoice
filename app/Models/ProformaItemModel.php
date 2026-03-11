<?php

namespace App\Models;

use CodeIgniter\Model;

class ProformaItemModel extends Model
{
    protected $table            = 'proforma_items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = ['proforma_id', 'billable_item_id', 'amount'];

    protected $validationRules = [
        'proforma_id'      => 'required|is_natural_no_zero',
        'billable_item_id' => 'required|is_natural_no_zero',
        'amount'           => 'required|decimal',
    ];
}

