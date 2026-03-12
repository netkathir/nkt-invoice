<?php

namespace App\Controllers;

use App\Models\BillableItemModel;

class DashboardController extends BaseController
{
    /**
     * @return array{month:string, label:string}
     */
    private function normalizeMonth(?string $month): array
    {
        $month = trim((string) ($month ?? ''));

        if (preg_match('/^\\d{4}-\\d{2}$/', $month) === 1) {
            $dt = \DateTime::createFromFormat('Y-m-d', $month . '-01') ?: new \DateTime('first day of this month');
            return [
                'month' => $dt->format('Y-m'),
                'label' => $dt->format('M Y'),
            ];
        }

        if (preg_match('/^[A-Za-z]{3}\\s\\d{4}$/', $month) === 1) {
            $dt = \DateTime::createFromFormat('M Y', $month) ?: new \DateTime('first day of this month');
            return [
                'month' => $dt->format('Y-m'),
                'label' => $dt->format('M Y'),
            ];
        }

        $dt = new \DateTime('first day of this month');
        return [
            'month' => $dt->format('Y-m'),
            'label' => $dt->format('M Y'),
        ];
    }

    private function applyMonthFilter($builder, string $yyyymm, string $monYear)
    {
        $db = db_connect();
        $builder->groupStart()
            ->where('billable_items.billing_month', $yyyymm)
            ->orWhere('billable_items.billing_month', $monYear)
            ->orWhere("DATE_FORMAT(billable_items.entry_date,'%Y-%m') = " . $db->escape($yyyymm), null, false)
            ->groupEnd();

        return $builder;
    }

    public function index()
    {
        $m = $this->normalizeMonth((string) $this->request->getGet('month'));

        return view('dashboard/index', [
            'active'                 => 'dashboard',
            'defaultMonth'           => $m['month'],
        ]);
    }

    public function metrics()
    {
        $m = $this->normalizeMonth((string) $this->request->getGet('month'));
        $yyyymm = $m['month'];
        $monYear = $m['label'];

        $b = db_connect()
            ->table('billable_items')
            ->select('COUNT(*) AS total_items', false)
            ->select("SUM(CASE WHEN status = '" . BillableItemModel::STATUS_PENDING . "' THEN 1 ELSE 0 END) AS pending_items", false)
            ->select("SUM(CASE WHEN status = '" . BillableItemModel::STATUS_BILLED . "' THEN 1 ELSE 0 END) AS billed_items", false)
            ->select("SUM(CASE WHEN status = '" . BillableItemModel::STATUS_PENDING . "' THEN amount ELSE 0 END) AS pending_amount", false)
            ->select("SUM(CASE WHEN status = '" . BillableItemModel::STATUS_BILLED . "' THEN amount ELSE 0 END) AS billed_amount", false);

        $this->applyMonthFilter($b, $yyyymm, $monYear);
        $row = $b->get()->getRowArray() ?: [];

        return $this->response->setJSON([
            'success' => true,
            'month'   => $yyyymm,
            'data'    => [
                'total_items'    => (int) ($row['total_items'] ?? 0),
                'pending_items'  => (int) ($row['pending_items'] ?? 0),
                'billed_items'   => (int) ($row['billed_items'] ?? 0),
                'pending_amount' => (float) ($row['pending_amount'] ?? 0),
                'billed_amount'  => (float) ($row['billed_amount'] ?? 0),
            ],
        ]);
    }

    public function pendingList()
    {
        $m = $this->normalizeMonth((string) $this->request->getGet('month'));
        $yyyymm = $m['month'];
        $monYear = $m['label'];

        $b = db_connect()
            ->table('billable_items')
            ->select('billable_items.id, billable_items.entry_no, billable_items.entry_date, clients.name AS client_name, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.amount, billable_items.status')
            ->join('clients', 'clients.id = billable_items.client_id', 'inner')
            ->where('billable_items.status', BillableItemModel::STATUS_PENDING);

        $this->applyMonthFilter($b, $yyyymm, $monYear);
        $rows = $b->orderBy('billable_items.entry_date', 'DESC')->orderBy('billable_items.id', 'DESC')->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['description'] = bms_description_to_plain($row['description'] ?? '');
        }
        unset($row);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function recentBilledList()
    {
        $m = $this->normalizeMonth((string) $this->request->getGet('month'));
        $yyyymm = $m['month'];
        $monYear = $m['label'];

        $b = db_connect()
            ->table('billable_items')
            ->select('billable_items.id, billable_items.entry_no, billable_items.entry_date, clients.name AS client_name, billable_items.amount, billable_items.updated_at AS billed_at, billable_items.status')
            ->join('clients', 'clients.id = billable_items.client_id', 'inner')
            ->where('billable_items.status', BillableItemModel::STATUS_BILLED);

        $this->applyMonthFilter($b, $yyyymm, $monYear);
        $rows = $b->orderBy('billable_items.updated_at', 'DESC')->orderBy('billable_items.id', 'DESC')->limit(50)->get()->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }
}
