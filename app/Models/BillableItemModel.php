<?php

namespace App\Models;

use CodeIgniter\Model;

class BillableItemModel extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_BILLED  = 'Billed';

    protected $table            = 'billable_items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = [
        'entry_no',
        'entry_date',
        'client_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'billing_month',
        'proforma_id',
        'invoice_id',
        'status',
    ];

    protected $validationRules = [
        'entry_no'      => 'permit_empty|max_length[20]',
        'entry_date'    => 'required|valid_date[Y-m-d]',
        'client_id'     => 'required|is_natural_no_zero',
        'description'   => 'required|min_length[2]',
        'quantity'      => 'required|decimal',
        'unit_price'    => 'required|decimal',
        'amount'        => 'required|decimal',
        // Supports new UI format "Mar 2026" and legacy "YYYY-MM"
        'billing_month' => 'permit_empty|regex_match[/^(\\d{4}-\\d{2}|(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\\s\\d{4})$/i]',
        'status'        => 'required|in_list[' . self::STATUS_PENDING . ',' . self::STATUS_BILLED . ']',
    ];
}
