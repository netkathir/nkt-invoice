<?php

namespace App\Models;

use CodeIgniter\Model;

class ProformaModel extends Model
{
    public const STATUS_DRAFT   = 'Draft';
    public const STATUS_POSTED  = 'Posted';
    public const TYPE_GST       = 'GST Invoice';
    public const TYPE_EXPORT    = 'Export Invoice';
    public const GST_MODE_CGST_SGST = 'CGST_SGST';
    public const GST_MODE_IGST      = 'IGST';

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
        'invoice_type',
        'billing_from',
        'billing_to',
        'currency',
        'gst_percent',
        'gst_mode',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_gst',
        'net_amount',
        'total_amount',
        'status',
    ];

    protected $validationRules = [
        'client_id'       => 'required|is_natural_no_zero',
        'proforma_number' => 'required|max_length[50]',
        'proforma_date'   => 'required|valid_date[Y-m-d]',
        'invoice_type'    => 'permit_empty|in_list[' . self::TYPE_GST . ',' . self::TYPE_EXPORT . ']',
        'billing_from'    => 'permit_empty|valid_date[Y-m-d]',
        'billing_to'      => 'permit_empty|valid_date[Y-m-d]',
        'currency'        => 'permit_empty|max_length[10]',
        'gst_percent'     => 'permit_empty|decimal',
        'gst_mode'        => 'permit_empty|in_list[' . self::GST_MODE_CGST_SGST . ',' . self::GST_MODE_IGST . ']',
        'cgst_amount'     => 'permit_empty|decimal',
        'sgst_amount'     => 'permit_empty|decimal',
        'igst_amount'     => 'permit_empty|decimal',
        'total_gst'       => 'permit_empty|decimal',
        'net_amount'      => 'permit_empty|decimal',
        'total_amount'    => 'required|decimal',
        'status'          => 'required|max_length[30]',
    ];

    public function nextProformaNumber(?string $date = null): string
    {
        $prefix = 'NKT';

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

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
