<?php

namespace App\Models;

use CodeIgniter\Model;

class DailyExpenseModel extends Model
{
    protected $table            = 'daily_expenses';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields    = [
        'expense_code',
        'expense_date',
        'category',
        'description',
        'remarks',
        'amount',
        'payment_method',
        'paid_to',
        'receipt_path',
    ];

    protected $validationRules = [
        'expense_code'    => 'required|max_length[30]',
        'expense_date'    => 'required|valid_date[Y-m-d]',
        'category'        => 'permit_empty|max_length[100]',
        'description'     => 'permit_empty|max_length[5000]',
        'remarks'         => 'permit_empty|max_length[5000]',
        'amount'          => 'required|decimal|greater_than[0]',
        'payment_method'  => 'permit_empty|max_length[50]',
        'paid_to'         => 'permit_empty|max_length[191]',
        'receipt_path'    => 'permit_empty|max_length[255]',
    ];
}
