<?php

namespace App\Controllers;

use App\Libraries\ProformaService;
use App\Libraries\SimplePdf;
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
        $nextInvoiceNo = (new ProformaModel())->nextProformaNumber();

        return view('proforma/create', [
            'active'  => 'proforma',
            'clients' => $clients,
            'nextInvoiceNo' => $nextInvoiceNo,
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
        $items = (new ProformaItemModel())
            ->select('billable_items.id, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.amount')
            ->join('billable_items', 'billable_items.id = proforma_items.billable_item_id')
            ->where('proforma_items.proforma_id', $id)
            ->orderBy('proforma_items.id', 'ASC')
            ->findAll();

        return view('proforma/edit_new', [
            'active'   => 'proforma',
            'clients'  => $clients,
            'proforma' => $proforma,
            'items'    => $items,
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
        $proformaNumber = trim((string) $this->request->getPost('proforma_number'));
        $items = $this->request->getPost('items');
        $itemIds = [];

        if ($proformaNumber === '') {
            $proformaNumber = '';
        }
        if ($clientId <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Client is required.']);
        }

        $createdBillableIds = [];
        if (is_array($items) && $items !== []) {
            $pfDate = $this->normalizeIsoDate((string) $this->request->getPost('proforma_date'));
            if ($pfDate === '') {
                $pfDate = date('Y-m-d');
            }

            $billables = new BillableItemModel();
            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $desc = trim((string) ($row['description'] ?? ''));
                $qty = (float) ($row['quantity'] ?? 0);
                $unitPrice = (float) ($row['unit_price'] ?? 0);
                $amount = (float) ($row['amount'] ?? ($qty * $unitPrice));

                if ($desc === '' && $qty === 0.0 && $unitPrice === 0.0 && $amount === 0.0) {
                    continue;
                }
                if ($desc === '') {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Item description is required.']);
                }
                if ($qty <= 0) {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Quantity must be greater than 0.']);
                }

                $id = $billables->insert([
                    'entry_date'  => $pfDate,
                    'client_id'   => $clientId,
                    'description' => $desc,
                    'quantity'    => number_format($qty, 2, '.', ''),
                    'unit_price'  => number_format($unitPrice, 2, '.', ''),
                    'amount'      => number_format($amount, 2, '.', ''),
                    'status'      => BillableItemModel::STATUS_PENDING,
                ], true);

                if (! $id) {
                    $errors = $billables->errors();
                    $msg = $errors ? (string) (array_values($errors)[0] ?? 'Invalid item.') : 'Invalid item.';
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $msg]);
                }

                $createdBillableIds[] = (int) $id;
            }

            $itemIds = $createdBillableIds;
        } else {
            $itemIds = (array) $this->request->getPost('item_ids');
            $itemIds = array_values(array_filter(array_map('intval', $itemIds)));
        }

        if ($clientId <= 0 || $itemIds === []) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Select a client and at least one item.']);
        }

        try {
            $service = new ProformaService(db_connect(), new BillableItemModel(), new ProformaModel(), new ProformaItemModel());
            $proforma = $service->create($clientId, $itemIds, [
                'proforma_number'=> $proformaNumber,
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
            if ($createdBillableIds !== []) {
                try {
                    (new BillableItemModel())->whereIn('id', $createdBillableIds)->delete();
                } catch (Throwable $ignored) {
                    // ignore cleanup errors
                }
            }
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        $proformaId = (int) $this->request->getPost('proforma_id');
        $proformaNumber = trim((string) $this->request->getPost('proforma_number'));
        $items = $this->request->getPost('items');
        $itemIds = [];

        if ($proformaId <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid invoice.']);
        }
        if ($proformaNumber === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invoice No is required.']);
        }

        $createdBillableIds = [];
        if (is_array($items) && $items !== []) {
            $proforma = (new ProformaModel())->find($proformaId);
            if (! $proforma) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice not found.']);
            }

            $clientId = (int) ($proforma['client_id'] ?? 0);
            if ($clientId <= 0) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid invoice client.']);
            }

            $targetClientId = (int) $this->request->getPost('client_id');
            if ($targetClientId > 0 && $targetClientId !== $clientId) {
                $exists = (new ClientModel())->find($targetClientId);
                if (! $exists) {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid client.']);
                }

                (new ProformaModel())->update($proformaId, ['client_id' => $targetClientId]);
                (new BillableItemModel())
                    ->where('proforma_id', $proformaId)
                    ->set(['client_id' => $targetClientId])
                    ->update();

                $clientId = $targetClientId;
            }

            $pfDate = $this->normalizeIsoDate((string) $this->request->getPost('proforma_date'));
            if ($pfDate === '') {
                $pfDate = date('Y-m-d');
            }

            $billables = new BillableItemModel();
            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $id = (int) ($row['id'] ?? 0);
                $desc = trim((string) ($row['description'] ?? ''));
                $qty = (float) ($row['quantity'] ?? 0);
                $unitPrice = (float) ($row['unit_price'] ?? 0);
                $amount = (float) ($row['amount'] ?? ($qty * $unitPrice));

                if ($desc === '' && $qty === 0.0 && $unitPrice === 0.0 && $amount === 0.0) {
                    continue;
                }
                if ($desc === '') {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Item description is required.']);
                }
                if ($qty <= 0) {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Quantity must be greater than 0.']);
                }

                if ($id > 0) {
                    $existing = $billables->find($id);
                    if (! $existing) {
                        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Some items are invalid.']);
                    }
                    if ((int) ($existing['proforma_id'] ?? 0) !== $proformaId) {
                        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Some items are not linked to this invoice.']);
                    }
                    if (! empty($existing['invoice_id'])) {
                        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Cannot edit items that are already invoiced.']);
                    }

                    $billables->update($id, [
                        'entry_date'  => $pfDate,
                        'client_id'   => $clientId,
                        'description' => $desc,
                        'quantity'    => number_format($qty, 2, '.', ''),
                        'unit_price'  => number_format($unitPrice, 2, '.', ''),
                        'amount'      => number_format($amount, 2, '.', ''),
                    ]);

                    $itemIds[] = $id;
                } else {
                    $newId = $billables->insert([
                        'entry_date'  => $pfDate,
                        'client_id'   => $clientId,
                        'description' => $desc,
                        'quantity'    => number_format($qty, 2, '.', ''),
                        'unit_price'  => number_format($unitPrice, 2, '.', ''),
                        'amount'      => number_format($amount, 2, '.', ''),
                        'status'      => BillableItemModel::STATUS_PENDING,
                    ], true);

                    if (! $newId) {
                        $errors = $billables->errors();
                        $msg = $errors ? (string) (array_values($errors)[0] ?? 'Invalid item.') : 'Invalid item.';
                        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $msg]);
                    }

                    $createdBillableIds[] = (int) $newId;
                    $itemIds[] = (int) $newId;
                }
            }

            $itemIds = array_values(array_filter(array_map('intval', $itemIds)));
        } else {
            $itemIds = (array) $this->request->getPost('item_ids');
            $itemIds = array_values(array_filter(array_map('intval', $itemIds)));
        }

        if ($itemIds === []) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Add at least one item.']);
        }

        try {
            $service = new ProformaService(db_connect(), new BillableItemModel(), new ProformaModel(), new ProformaItemModel());
            $proforma = $service->update($proformaId, $itemIds, [
                'proforma_number'=> $proformaNumber,
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
                'message'  => 'Invoice updated.',
                'proforma' => $proforma,
            ]);
        } catch (Throwable $e) {
            if ($createdBillableIds !== []) {
                try {
                    (new BillableItemModel())->whereIn('id', $createdBillableIds)->delete();
                } catch (Throwable $ignored) {
                    // ignore cleanup errors
                }
            }
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function show(int $id)
    {
        $proforma = (new ProformaModel())
            ->select('proforma_invoices.*, clients.name as client_name, clients.contact_person, clients.email, clients.phone, clients.gst_no, clients.address, clients.billing_address, clients.city, clients.state, clients.country, clients.postal_code')
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

    public function print(int $id)
    {
        $autoprint = (string) $this->request->getGet('autoprint') === '1';

        $proforma = (new ProformaModel())
            ->select('proforma_invoices.*, clients.name as client_name, clients.contact_person, clients.email, clients.phone, clients.gst_no, clients.address, clients.billing_address, clients.city, clients.state, clients.country, clients.postal_code')
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
            ->orderBy('proforma_items.id', 'ASC')
            ->findAll();

        return view('proforma/print_standard', [
            'title'     => 'Invoice ' . (string) ($proforma['proforma_number'] ?? ''),
            'proforma'  => $proforma,
            'items'     => $items,
            'autoprint' => $autoprint,
        ]);
    }

    public function pdf(int $id)
    {
        $proforma = (new ProformaModel())
            ->select('proforma_invoices.*, clients.name as client_name, clients.contact_person, clients.email, clients.phone, clients.gst_no, clients.address, clients.billing_address, clients.city, clients.state, clients.country, clients.postal_code')
            ->join('clients', 'clients.id = proforma_invoices.client_id')
            ->where('proforma_invoices.id', $id)
            ->first();

        if (! $proforma) {
            return $this->response->setStatusCode(404)->setBody('Invoice not found.');
        }

        $items = (new ProformaItemModel())
            ->select('proforma_items.amount, billable_items.entry_date, billable_items.description, billable_items.quantity, billable_items.unit_price, billable_items.billing_month')
            ->join('billable_items', 'billable_items.id = proforma_items.billable_item_id')
            ->where('proforma_items.proforma_id', $id)
            ->orderBy('proforma_items.id', 'ASC')
            ->findAll();

        $currency = (string) (($proforma['currency'] ?? '') ?: 'INR');
        // Use ASCII prefix for maximum PDF font compatibility (standard Helvetica is WinAnsi).
        $moneyPrefix = $currency . ' ';

        $fromName = (string) (config('Email')->fromName ?? 'Billing Management System');
        $fromEmail = (string) (config('Email')->fromEmail ?? '');

        $invoiceType = (string) (($proforma['invoice_type'] ?? '') ?: 'Invoice');
        $invoiceNo = (string) (($proforma['proforma_number'] ?? '') ?: '');
        $issueDate = (string) (($proforma['proforma_date'] ?? '') ?: '');

        $billToName = (string) (($proforma['client_name'] ?? '') ?: '');
        $billToContact = (string) (($proforma['contact_person'] ?? '') ?: '');
        $billToEmail = (string) (($proforma['email'] ?? '') ?: '');
        $billToPhone = (string) (($proforma['phone'] ?? '') ?: '');
        $billToAddr1 = trim((string) (($proforma['billing_address'] ?? '') ?: ($proforma['address'] ?? '')));
        $billToCity = (string) (($proforma['city'] ?? '') ?: '');
        $billToState = (string) (($proforma['state'] ?? '') ?: '');
        $billToCountry = (string) (($proforma['country'] ?? '') ?: '');
        $billToPostal = (string) (($proforma['postal_code'] ?? '') ?: '');

        $subTotal = (float) ($proforma['total_amount'] ?? 0);
        $netAmount = (float) (($proforma['net_amount'] ?? null) ?? $subTotal);
        $totalGst = (float) (($proforma['total_gst'] ?? null) ?? 0);
        $cgst = (float) (($proforma['cgst_amount'] ?? null) ?? 0);
        $sgst = (float) (($proforma['sgst_amount'] ?? null) ?? 0);
        $igst = (float) (($proforma['igst_amount'] ?? null) ?? 0);
        $gstPercent = (float) (($proforma['gst_percent'] ?? null) ?? 0);
        $gstMode = (string) (($proforma['gst_mode'] ?? '') ?: '');

        $pdf = new SimplePdf();
        $pdf->addPage();

        $pageW = 595.28;
        $pageH = 841.89;
        $margin = 36.0;
        $xL = $margin;
        $xR = $pageW - $margin;
        $y = 48.0;

        // Header (left)
        $pdf->setFont('Helvetica', 'B', 12);
        $pdf->text($xL, $y, $fromName);
        $y += 16;
        if ($fromEmail !== '') {
            $pdf->setFont('Helvetica', '', 10);
            $pdf->setTextColor(80, 80, 80);
            $pdf->text($xL, $y, $fromEmail);
            $pdf->setTextColor(0, 0, 0);
        }

        // Header (right)
        $pdf->setFont('Helvetica', 'B', 18);
        $title = strtoupper($invoiceType);
        $pdf->text($xR - $pdf->estimateTextWidth($title), 48.0, $title);

        $pdf->setFont('Helvetica', '', 10);
        $meta1 = 'Invoice No: ' . $invoiceNo;
        $meta2 = $issueDate !== '' ? ('Date: ' . $issueDate) : '';
        $pdf->setTextColor(80, 80, 80);
        $pdf->text($xR - $pdf->estimateTextWidth($meta1), 70.0, $meta1);
        if ($meta2 !== '') {
            $pdf->text($xR - $pdf->estimateTextWidth($meta2), 86.0, $meta2);
        }
        $pdf->setTextColor(0, 0, 0);

        // Divider
        $pdf->setDrawColor(180, 180, 180);
        $pdf->setLineWidth(1.0);
        $pdf->line($xL, 108.0, $xR, 108.0);

        // Bill To
        $y = 132.0;
        $pdf->setFont('Helvetica', '', 9);
        $pdf->setTextColor(90, 90, 90);
        $pdf->text($xL, $y, 'Bill To');
        $pdf->setTextColor(0, 0, 0);
        $y += 16;

        $pdf->setFont('Helvetica', 'B', 11);
        $pdf->text($xL, $y, $billToName);
        $y += 15;
        $pdf->setFont('Helvetica', '', 10);
        if ($billToContact !== '') {
            $pdf->text($xL, $y, $billToContact);
            $y += 14;
        }
        if ($billToAddr1 !== '') {
            foreach (preg_split('/\\r?\\n/', $billToAddr1) ?: [] as $ln) {
                $ln = trim((string) $ln);
                if ($ln === '') continue;
                $pdf->text($xL, $y, $ln);
                $y += 14;
            }
        }
        $place = trim(implode(', ', array_values(array_filter([$billToCity, $billToState, $billToCountry]))));
        $placeLine = trim($place . ($billToPostal !== '' ? (' - ' . $billToPostal) : ''));
        if ($placeLine !== '') {
            $pdf->text($xL, $y, $placeLine);
            $y += 14;
        }
        $contactLine = trim($billToEmail . ($billToEmail !== '' && $billToPhone !== '' ? ' / ' : '') . $billToPhone);
        if ($contactLine !== '') {
            $pdf->setTextColor(90, 90, 90);
            $pdf->text($xL, $y, $contactLine);
            $pdf->setTextColor(0, 0, 0);
            $y += 14;
        }

        // Right meta block (no Status)
        $yR = 132.0;

        $pdf->setFont('Helvetica', '', 9);
        $pdf->setTextColor(90, 90, 90);
        $pdf->text($xR - $pdf->estimateTextWidth('Currency'), $yR, 'Currency');
        $yR += 16;
        $pdf->setFont('Helvetica', 'B', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->text($xR - $pdf->estimateTextWidth($currency), $yR, $currency);
        $yR += 22;

        $billingFrom = (string) (($proforma['billing_from'] ?? '') ?: '-');
        $billingTo = (string) (($proforma['billing_to'] ?? '') ?: '-');
        $pdf->setFont('Helvetica', '', 9);
        $pdf->setTextColor(90, 90, 90);
        $pdf->text($xR - $pdf->estimateTextWidth('Billing Period'), $yR, 'Billing Period');
        $yR += 16;
        $pdf->setFont('Helvetica', 'B', 10);
        $pdf->setTextColor(0, 0, 0);
        $bp = $billingFrom . ' to ' . $billingTo;
        $pdf->text($xR - $pdf->estimateTextWidth($bp), $yR, $bp);

        // Items table
        $tableTop = max($y + 18.0, 250.0);
        $x0 = $xL;
        $wNo = 28.0;
        $wDesc = 255.0;
        $wQty = 55.0;
        $wUnit = 85.0;
        $wAmt = ($xR - $x0) - ($wNo + $wDesc + $wQty + $wUnit);
        $rowH = 20.0;
        $lineH = 12.0;

        $drawHeader = function (float $top) use ($pdf, $x0, $xR, $wNo, $wDesc, $wQty, $wUnit, $wAmt, $rowH): void {
            $pdf->setDrawColor(160, 160, 160);
            $pdf->setFillColor(245, 245, 245);
            $pdf->setLineWidth(1.0);
            $pdf->rect($x0, $top, $xR - $x0, $rowH, true, true);

            $pdf->setFont('Helvetica', 'B', 10);
            $pdf->text($x0 + 8, $top + 14, 'S.No');
            $pdf->text($x0 + $wNo + 6, $top + 14, 'Description');
            $pdf->text($x0 + $wNo + $wDesc + 10, $top + 14, 'Qty');
            $pdf->text($x0 + $wNo + $wDesc + $wQty + 10, $top + 14, 'Unit Price');
            $pdf->text($x0 + $wNo + $wDesc + $wQty + $wUnit + 10, $top + 14, 'Amount');
        };

        $yT = $tableTop;
        $drawHeader($yT);
        $yT += $rowH;

        $pdf->setFont('Helvetica', '', 10);
        $pdf->setDrawColor(200, 200, 200);
        $pdf->setLineWidth(1.0);

        $idx = 1;
        foreach ($items as $it) {
            $descRaw = (string) ($it['description'] ?? '');
            $lines = array_values(array_filter(array_map('trim', preg_split('/\\r?\\n/', $descRaw) ?: [])));
            $itemName = $lines[0] ?? '';
            $rest = $lines;
            if ($rest !== []) {
                array_shift($rest);
            }

            $descLines = [];
            if ($itemName !== '') {
                $descLines[] = [$itemName, true];
            }
            foreach ($rest as $b) {
                $b = trim($b);
                if ($b === '') continue;
                $wrapped = $pdf->wrapText('- ' . $b, $wDesc - 12, 9);
                foreach ($wrapped as $wl) {
                    $descLines[] = [$wl, false];
                }
            }
            if ($descLines === []) {
                $descLines[] = ['-', false];
            }

            $descHeight = (count($descLines) * $lineH) + 6.0;
            $h = max($rowH, $descHeight);

            // Page break (simple)
            if (($yT + $h + 160.0) > $pageH) {
                $pdf->addPage();
                $yT = 48.0;
                $drawHeader($yT);
                $yT += $rowH;
            }

            // Row border
            $pdf->setFillColor(255, 255, 255);
            $pdf->rect($x0, $yT, $xR - $x0, $h, true, false);

            // Column separators (optional light lines)
            $pdf->setDrawColor(220, 220, 220);
            $pdf->line($x0 + $wNo, $yT, $x0 + $wNo, $yT + $h);
            $pdf->line($x0 + $wNo + $wDesc, $yT, $x0 + $wNo + $wDesc, $yT + $h);
            $pdf->line($x0 + $wNo + $wDesc + $wQty, $yT, $x0 + $wNo + $wDesc + $wQty, $yT + $h);
            $pdf->line($x0 + $wNo + $wDesc + $wQty + $wUnit, $yT, $x0 + $wNo + $wDesc + $wQty + $wUnit, $yT + $h);

            // Cells
            $pdf->setFont('Helvetica', '', 10);
            $pdf->text($x0 + 8, $yT + 14, (string) $idx);

            $yy = $yT + 14;
            foreach ($descLines as $dl) {
                [$txt, $bold] = $dl;
                $pdf->setFont('Helvetica', $bold ? 'B' : '', $bold ? 10 : 9);
                $pdf->text($x0 + $wNo + 6, $yy, (string) $txt);
                $yy += $lineH;
            }

            $qtyTxt = number_format((float) ($it['quantity'] ?? 0), 2);
            $unitTxt = $moneyPrefix . number_format((float) ($it['unit_price'] ?? 0), 2);
            $amtTxt = $moneyPrefix . number_format((float) ($it['amount'] ?? 0), 2);

            $pdf->setFont('Helvetica', '', 10);
            $pdf->text($x0 + $wNo + $wDesc + $wQty - 8 - $pdf->estimateTextWidth($qtyTxt), $yT + 14, $qtyTxt);
            $pdf->text($x0 + $wNo + $wDesc + $wQty + $wUnit - 8 - $pdf->estimateTextWidth($unitTxt), $yT + 14, $unitTxt);
            $pdf->text($xR - 8 - $pdf->estimateTextWidth($amtTxt), $yT + 14, $amtTxt);

            $yT += $h;
            $idx++;
        }

        // Totals
        $yTotals = $yT + 22.0;
        if (($yTotals + 140.0) > $pageH) {
            $pdf->addPage();
            $yTotals = 80.0;
        }

        $pdf->setDrawColor(180, 180, 180);
        $pdf->line($x0, $yTotals - 10.0, $xR, $yTotals - 10.0);

        $labelX = $xR - 220.0;
        $valX = $xR;

        $row = function (string $label, string $value) use ($pdf, $labelX, $valX, &$yTotals): void {
            $pdf->setFont('Helvetica', '', 10);
            $pdf->setTextColor(90, 90, 90);
            $pdf->text($labelX, $yTotals, $label);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFont('Helvetica', 'B', 10);
            $pdf->text($valX - $pdf->estimateTextWidth($value), $yTotals, $value);
            $yTotals += 16.0;
        };

        $row('Sub Total', $moneyPrefix . number_format($subTotal, 2));
        if ($gstPercent > 0 || $totalGst > 0) {
            $gstLabel = 'GST (' . number_format($gstPercent, 2) . '%)' . ($gstMode !== '' ? (' / ' . $gstMode) : '');
            $row($gstLabel, $moneyPrefix . number_format($totalGst, 2));
            if ($cgst > 0) $row('CGST', $moneyPrefix . number_format($cgst, 2));
            if ($sgst > 0) $row('SGST', $moneyPrefix . number_format($sgst, 2));
            if ($igst > 0) $row('IGST', $moneyPrefix . number_format($igst, 2));
        }

        $pdf->setFont('Helvetica', 'B', 12);
        $pdf->setTextColor(0, 0, 0);
        $pdf->text($labelX, $yTotals + 6.0, 'Net Amount');
        $netTxt = $moneyPrefix . number_format($netAmount, 2);
        $pdf->text($valX - $pdf->estimateTextWidth($netTxt, 12), $yTotals + 6.0, $netTxt);
        $yTotals += 34.0;

        $pdf->setDrawColor(180, 180, 180);
        $sigW = 200.0;
        $pdf->line($xR - $sigW, $yTotals + 34.0, $xR, $yTotals + 34.0);
        $pdf->setFont('Helvetica', '', 10);
        $pdf->setTextColor(90, 90, 90);
        $pdf->text($xR - $sigW + 32.0, $yTotals + 50.0, 'Authorized Signature');

        $bin = $pdf->output();
        $fileNo = preg_replace('/[^A-Za-z0-9\\-_.]+/', '-', $invoiceNo) ?: ('invoice-' . $id);
        $filename = 'Invoice-' . $fileNo . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setHeader('Pragma', 'public')
            ->setBody($bin);
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
