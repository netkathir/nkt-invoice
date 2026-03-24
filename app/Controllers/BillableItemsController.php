<?php

namespace App\Controllers;

use App\Libraries\ProformaService;
use App\Models\BillableItemModel;
use App\Models\ClientModel;
use App\Models\ProformaItemModel;
use App\Models\ProformaModel;
use Throwable;

class BillableItemsController extends BaseController
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

    private function formatEntryNo(int $id): string
    {
        return 'BI-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        return view('billable_items/list', [
            'active'  => 'billable_items',
        ]);
    }

    public function list()
    {
        $clientId = (int) $this->request->getGet('client_id');
        $status = trim((string) $this->request->getGet('status'));
        $monthParam = trim((string) $this->request->getGet('month'));

        $billableItems = new BillableItemModel();
        $builder = $billableItems
            ->select("billable_items.*, COALESCE(NULLIF(TRIM(clients.name),''), clients.contact_person, clients.email, CONCAT('Client #', clients.id)) as client_name", false)
            ->join('clients', 'clients.id = billable_items.client_id', 'left')
            ->orderBy('billable_items.id', 'DESC');

        if ($clientId > 0) {
            $builder->where('billable_items.client_id', $clientId);
        }

        if ($status !== '') {
            $builder->where('billable_items.status', $status);
        }

        if ($monthParam !== '') {
            $m = $this->normalizeMonth($monthParam);
            $this->applyMonthFilter($builder, $m['month'], $m['label']);
        }

        $rows = $builder->findAll();

        // Always return plain text descriptions for UI rendering.
        foreach ($rows as &$row) {
            $row['description'] = bms_description_to_plain($row['description'] ?? '');
        }
        unset($row);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function save()
    {
        $billableItems = new BillableItemModel();

        $id = (int) $this->request->getPost('id');
        $quantity = (float) $this->request->getPost('quantity');
        $unitPrice = (float) $this->request->getPost('unit_price');
        $amount = $quantity * $unitPrice;
        $entryDate = trim((string) $this->request->getPost('entry_date'));
        if ($entryDate === '') {
            if ($id > 0) {
                $existing = $billableItems->select('entry_date')->find($id);
                $entryDate = (string) ($existing['entry_date'] ?? '');
            }
            if ($entryDate === '') {
                $entryDate = date('Y-m-d');
            }
        }
        $billingMonth = trim((string) $this->request->getPost('billing_month'));
        if ($billingMonth === 'YYYY-MM' || preg_match('/^y{4}-m{2}$/i', $billingMonth) === 1) {
            $billingMonth = '';
        }

        $rawDescription = (string) $this->request->getPost('description');
        $plainDescription = bms_description_to_plain($rawDescription);

        $payload = [
            'entry_date'    => $entryDate,
            'client_id'     => (int) $this->request->getPost('client_id'),
            'description'   => $plainDescription,
            'quantity'      => number_format($quantity, 2, '.', ''),
            'unit_price'    => number_format($unitPrice, 2, '.', ''),
            'amount'        => number_format($amount, 2, '.', ''),
            'billing_month' => $billingMonth,
            'currency'      => trim((string) $this->request->getPost('currency')) ?: 'INR',
            'status'        => trim((string) $this->request->getPost('status')) ?: BillableItemModel::STATUS_PENDING,
        ];

        if ($id > 0) {
            $payload['id'] = $id;
        }

        try {
            if (trim((string) ($payload['description'] ?? '')) === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please fix the validation errors.',
                    'errors'  => ['description' => 'Description is required.'],
                ]);
            }

            if (! $billableItems->save($payload)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please fix the validation errors.',
                    'errors'  => $billableItems->errors(),
                ]);
            }

            $savedId = $id > 0 ? $id : (int) $billableItems->getInsertID();

            if ($savedId > 0) {
                // Auto-generate entry number based on ID (only if missing)
                $row = $billableItems->select('id, entry_no')->find($savedId);
                if ($row && empty($row['entry_no'])) {
                    $billableItems->update($savedId, ['entry_no' => $this->formatEntryNo($savedId)]);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $id > 0 ? 'Billable item updated.' : 'Billable item added.',
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        $billableItems = new BillableItemModel();

        $id = (int) $this->request->getPost('id');
        $field = trim((string) $this->request->getPost('field'));
        $value = (string) $this->request->getPost('value');

        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid billable item.']);
        }

        $allowed = ['entry_date', 'description', 'quantity', 'unit_price', 'billing_month'];
        if (! in_array($field, $allowed, true)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Field not editable.']);
        }

        $existing = $billableItems->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Billable item not found.']);
        }

        if (($existing['status'] ?? null) !== BillableItemModel::STATUS_PENDING) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Only Pending items can be edited.']);
        }

        $payload = [$field => $value];
        if ($field === 'description') {
            $payload['description'] = bms_description_to_plain((string) $value);
            if (trim((string) ($payload['description'] ?? '')) === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please fix the validation errors.',
                    'errors'  => ['description' => 'Description is required.'],
                ]);
            }
        }
        if ($field === 'quantity' || $field === 'unit_price') {
            $quantity = (float) ($field === 'quantity' ? $value : $existing['quantity']);
            $unitPrice = (float) ($field === 'unit_price' ? $value : $existing['unit_price']);
            $payload['quantity'] = number_format($quantity, 2, '.', '');
            $payload['unit_price'] = number_format($unitPrice, 2, '.', '');
            $payload['amount'] = number_format($quantity * $unitPrice, 2, '.', '');
        }

        try {
            if (! $billableItems->update($id, $payload)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please fix the validation errors.',
                    'errors'  => $billableItems->errors(),
                ]);
            }

            $updated = $billableItems->find($id);
            return $this->response->setJSON(['success' => true, 'message' => 'Updated.', 'row' => $updated]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid billable item.']);
        }

        $billableItems = new BillableItemModel();
        $existing = $billableItems->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Billable item not found.']);
        }

        if (($existing['status'] ?? null) !== BillableItemModel::STATUS_PENDING) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Only Pending items can be deleted.']);
        }

        try {
            $billableItems->delete($id);
            return $this->response->setJSON(['success' => true, 'message' => 'Billable item deleted.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function generateProforma()
    {
        $ids = (array) $this->request->getPost('item_ids');
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Select at least one item.']);
        }

        $billableItems = new BillableItemModel();
        $rows = $billableItems->select('id, client_id')->whereIn('id', $ids)->findAll();
        if ($rows === []) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'No items found.']);
        }

        $clientId = (int) ($rows[0]['client_id'] ?? 0);
        foreach ($rows as $row) {
            if ((int) ($row['client_id'] ?? 0) !== $clientId) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Select items for a single client.']);
            }
        }

        try {
            $service = new ProformaService(db_connect(), new BillableItemModel(), new ProformaModel(), new ProformaItemModel());
            $proforma = $service->create($clientId, $ids, [
                'proforma_date' => (string) $this->request->getPost('proforma_date'),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Proforma created.',
                'proforma'=> $proforma,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markBilled()
    {
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid billable item.']);
        }

        $billableItems = new BillableItemModel();
        $existing = $billableItems->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Billable item not found.']);
        }

        if (($existing['status'] ?? null) !== BillableItemModel::STATUS_PENDING) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Only Pending items can be marked as Billed.']);
        }

        try {
            $billableItems->update($id, ['status' => BillableItemModel::STATUS_BILLED]);
            return $this->response->setJSON(['success' => true, 'message' => 'Marked as Billed.']);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
