<?php

namespace App\Controllers;

use App\Models\BillableItemModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $billableItems = new BillableItemModel();

        $totalBillableItems = $billableItems->countAllResults();
        $pendingBilling = $billableItems->where('status', BillableItemModel::STATUS_PENDING)->countAllResults();
        $billedItems = $billableItems->where('status', BillableItemModel::STATUS_BILLED)->countAllResults();

        $totalBillingAmountRow = $billableItems->selectSum('amount', 'total')->first();
        $totalBillingAmount = (string) ($totalBillingAmountRow['total'] ?? '0.00');

        return view('dashboard/index', [
            'active'                 => 'dashboard',
            'totalBillableItems'     => $totalBillableItems,
            'pendingBilling'         => $pendingBilling,
            'billedItems'            => $billedItems,
            'totalBillingAmount'     => $totalBillingAmount,
        ]);
    }

    public function recentBillableItems()
    {
        $billableItems = new BillableItemModel();

        $rows = $billableItems
            ->select('billable_items.id, billable_items.entry_no, billable_items.entry_date, clients.name as client_name, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.amount, billable_items.status')
            ->join('clients', 'clients.id = billable_items.client_id')
            ->orderBy('billable_items.id', 'DESC')
            ->limit(50)
            ->findAll();

        foreach ($rows as &$row) {
            $row['description'] = bms_description_to_plain($row['description'] ?? '');
        }
        unset($row);

        return $this->response->setJSON(['data' => $rows]);
    }
}
