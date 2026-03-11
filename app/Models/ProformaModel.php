<?php

namespace App\Models;

use CodeIgniter\Model;

class ProformaModel extends Model
{
    public const STATUS_DRAFT   = 'Draft';
    public const STATUS_POSTED  = 'Posted';

    protected $table            = 'proforma_invoices';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = [
        'proforma_number',
        'client_id',
        'proforma_date',
        'billing_from',
        'billing_to',
        'total_amount',
        'status',
    ];

    protected $validationRules = [
        'client_id'       => 'required|is_natural_no_zero',
        'proforma_number' => 'required|max_length[50]',
        'proforma_date'   => 'required|valid_date[Y-m-d]',
        'billing_from'    => 'permit_empty|valid_date[Y-m-d]',
        'billing_to'      => 'permit_empty|valid_date[Y-m-d]',
        'total_amount'    => 'required|decimal',
        'status'          => 'required|max_length[30]',
    ];

    public function nextProformaNumber(?string $date = null): string
    {
        $date = $date ?: date('Y-m-d');
        $datePart = date('Ymd', strtotime($date));
        $prefix = "PF-{$datePart}-";

        $row = $this->select('proforma_number')
            ->like('proforma_number', $prefix, 'after')
            ->orderBy('proforma_number', 'DESC')
            ->first();

        $next = 1;
        if ($row && isset($row['proforma_number'])) {
            $existing = (string) $row['proforma_number'];
            $suffix = substr($existing, strlen($prefix));
            if (ctype_digit($suffix)) {
                $next = (int) $suffix + 1;
            }
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
