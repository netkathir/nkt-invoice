<?php

namespace App\Controllers;

use App\Libraries\ProformaService;
use App\Models\BillableItemModel;
use App\Models\ClientModel;
use App\Models\ProformaItemModel;
use App\Models\ProformaModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Throwable;

class ProformaController extends BaseController
{
    private function normalizeIsoDate(?string $value): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return '';
        }

        // Already ISO
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }

        // Accept DD/MM/YYYY or DD-MM-YYYY from UI
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $raw, $m)) {
            $dd = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mm = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $yyyy = $m[3];
            return $yyyy . '-' . $mm . '-' . $dd;
        }

        return $raw;
    }

    public function index()
    {
        return view('proforma/index', ['active' => 'proforma']);
    }

    public function list()
    {
        $rows = (new ProformaModel())
            ->select('proforma_invoices.*, clients.name as client_name, clients.name as company_name, clients.contact_person as customer_name')
            ->join('clients', 'clients.id = proforma_invoices.client_id')
            ->orderBy('proforma_invoices.id', 'DESC')
            ->findAll();

        return $this->response->setJSON(['data' => $rows]);
    }

    public function create()
    {
        $clients = (new ClientModel())->orderBy('name', 'ASC')->findAll();

        return view('proforma/create', [
            'active'  => 'proforma',
            'clients' => $clients,
        ]);
    }

    public function edit(int $id)
    {
        $proforma = (new ProformaModel())
            ->select('proforma_invoices.*, clients.name as client_name')
            ->join('clients', 'clients.id = proforma_invoices.client_id')
            ->where('proforma_invoices.id', $id)
            ->first();

        if (! $proforma) {
            return redirect()->to('/proforma')->with('error', 'Invoice not found.');
        }

        $clients = (new ClientModel())->orderBy('name', 'ASC')->findAll();

        return view('proforma/edit', [
            'active'   => 'proforma',
            'clients'  => $clients,
            'proforma' => $proforma,
        ]);
    }

    public function pendingItems()
    {
        $clientId = (int) $this->request->getGet('client_id');
        if ($clientId <= 0) {
            return $this->response->setJSON(['data' => []]);
        }

        $rows = (new BillableItemModel())
            ->select('billable_items.id, billable_items.entry_no, billable_items.entry_date, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.amount, billable_items.billing_month')
            ->where('billable_items.client_id', $clientId)
            ->where('billable_items.status', BillableItemModel::STATUS_PENDING)
            ->where('billable_items.proforma_id', null)
            ->orderBy('billable_items.entry_date', 'DESC')
            ->findAll();

        return $this->response->setJSON(['data' => $rows]);
    }

    // Endpoint requested by spec: /proforma/getPendingItems/{client_id}
    public function getPendingItems(int $clientId)
    {
        $rows = (new BillableItemModel())
            ->select('billable_items.id, billable_items.entry_no, billable_items.entry_date, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.amount, billable_items.billing_month')
            ->where('billable_items.client_id', $clientId)
            ->where('billable_items.status', BillableItemModel::STATUS_PENDING)
            ->where('billable_items.proforma_id', null)
            ->orderBy('billable_items.entry_date', 'DESC')
            ->findAll();

        return $this->response->setJSON(['data' => $rows]);
    }

    public function editItems()
    {
        $clientId = (int) $this->request->getGet('client_id');
        $proformaId = (int) $this->request->getGet('proforma_id');

        if ($clientId <= 0 || $proformaId <= 0) {
            return $this->response->setJSON(['data' => []]);
        }

        $rows = (new BillableItemModel())
            ->select('billable_items.id, billable_items.entry_no, billable_items.entry_date, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.amount, billable_items.billing_month, billable_items.proforma_id')
            ->where('billable_items.client_id', $clientId)
            ->groupStart()
                ->groupStart()
                    ->where('billable_items.status', BillableItemModel::STATUS_PENDING)
                    ->where('billable_items.proforma_id', null)
                ->groupEnd()
                ->orGroupStart()
                    ->where('billable_items.proforma_id', $proformaId)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('billable_items.entry_date', 'DESC')
            ->orderBy('billable_items.id', 'DESC')
            ->findAll();

        foreach ($rows as &$r) {
            $r['included'] = ((int) ($r['proforma_id'] ?? 0) === $proformaId) ? 1 : 0;
            unset($r['proforma_id']);
        }
        unset($r);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function save()
    {
        $clientId = (int) $this->request->getPost('client_id');
        $itemIds = (array) $this->request->getPost('item_ids');
        $itemIds = array_values(array_filter(array_map('intval', $itemIds)));

        if ($clientId <= 0 || $itemIds === []) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Select a client and at least one item.']);
        }

        try {
            $service = new ProformaService(db_connect(), new BillableItemModel(), new ProformaModel(), new ProformaItemModel());
            $proforma = $service->create($clientId, $itemIds, [
                'proforma_date' => $this->normalizeIsoDate((string) $this->request->getPost('proforma_date')),
                'invoice_type'  => (string) $this->request->getPost('invoice_type'),
                'billing_from'  => $this->normalizeIsoDate((string) $this->request->getPost('billing_from')),
                'billing_to'    => $this->normalizeIsoDate((string) $this->request->getPost('billing_to')),
                'currency'      => (string) $this->request->getPost('currency'),
                'gst_percent'   => (string) $this->request->getPost('gst_percent'),
                'gst_mode'      => (string) $this->request->getPost('gst_mode'),
            ]);

            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'Invoice created.',
                'proforma' => $proforma,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        $proformaId = (int) $this->request->getPost('proforma_id');
        $itemIds = (array) $this->request->getPost('item_ids');
        $itemIds = array_values(array_filter(array_map('intval', $itemIds)));

        if ($proformaId <= 0 || $itemIds === []) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Select at least one item.']);
        }

        try {
            $service = new ProformaService(db_connect(), new BillableItemModel(), new ProformaModel(), new ProformaItemModel());
            $proforma = $service->update($proformaId, $itemIds, [
                'proforma_date' => $this->normalizeIsoDate((string) $this->request->getPost('proforma_date')),
                'invoice_type'  => (string) $this->request->getPost('invoice_type'),
                'billing_from'  => $this->normalizeIsoDate((string) $this->request->getPost('billing_from')),
                'billing_to'    => $this->normalizeIsoDate((string) $this->request->getPost('billing_to')),
                'currency'      => (string) $this->request->getPost('currency'),
                'gst_percent'   => (string) $this->request->getPost('gst_percent'),
                'gst_mode'      => (string) $this->request->getPost('gst_mode'),
                'status'        => (string) $this->request->getPost('status'),
            ]);

            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'Invoice updated.',
                'proforma' => $proforma,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function show(int $id)
    {
        $proforma = (new ProformaModel())
            ->select('proforma_invoices.*, clients.name as client_name, clients.contact_person, clients.email, clients.phone')
            ->join('clients', 'clients.id = proforma_invoices.client_id')
            ->where('proforma_invoices.id', $id)
            ->first();

        if (! $proforma) {
            return redirect()->to('/proforma')->with('error', 'Invoice not found.');
        }

        $items = (new ProformaItemModel())
            ->select('proforma_items.amount, billable_items.entry_date, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.billing_month')
            ->join('billable_items', 'billable_items.id = proforma_items.billable_item_id')
            ->where('proforma_items.proforma_id', $id)
            ->orderBy('billable_items.entry_date', 'ASC')
            ->findAll();

        return view('proforma/show', [
            'active'  => 'proforma',
            'proforma'=> $proforma,
            'items'   => $items,
        ]);
    }

    public function delete()
    {
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid proforma.']);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $proforma = (new ProformaModel())->find($id);
            if (! $proforma) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice not found.']);
            }

            $piModel = new ProformaItemModel();
            $rows = $piModel->select('billable_item_id')->where('proforma_id', $id)->findAll();
            $billableIds = array_values(array_filter(array_map(static fn ($r) => (int) ($r['billable_item_id'] ?? 0), $rows)));

            if ($billableIds !== []) {
                (new BillableItemModel())
                    ->whereIn('id', $billableIds)
                    ->set([
                        'status'      => BillableItemModel::STATUS_PENDING,
                        'proforma_id' => null,
                    ])
                    ->update();
            }

            // Deleting proforma_invoices will cascade delete proforma_items (FK).
            (new ProformaModel())->delete($id);

            if ($db->transStatus() === false) {
                throw new DatabaseException('Database error while deleting proforma.');
            }

            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Invoice deleted.']);
        } catch (Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
