<?php

namespace App\Controllers;

use App\Libraries\ProformaService;
use App\Libraries\SimplePdf;
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

    private function formatPdfDate(?string $value, string $fallback = '-'): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return $fallback;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', substr($raw, 0, 10));
        if ($dt instanceof \DateTime) {
            return $dt->format('d M Y');
        }

        return $raw;
    }

    private function formatPdfMonth(?string $value, string $fallback = '-'): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return $fallback;
        }

        if (preg_match('/^\\d{4}-\\d{2}$/', $raw) === 1) {
            $dt = \DateTime::createFromFormat('Y-m', $raw);
            if ($dt instanceof \DateTime) {
                return $dt->format('M Y');
            }
        }

        if (preg_match('/^[A-Za-z]{3}\\s\\d{4}$/', $raw) === 1) {
            $dt = \DateTime::createFromFormat('M Y', $raw);
            if ($dt instanceof \DateTime) {
                return $dt->format('M Y');
            }
        }

        return $raw;
    }

    /**
     * @return string[]
     */
    private function extractDescriptionPoints(?string $value): array
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return [];
        }

        $maybeHtml = $value;
        if (strpos($maybeHtml, '<') === false && stripos($maybeHtml, '&lt;') !== false) {
            $decoded = html_entity_decode($maybeHtml, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (strpos($decoded, '<') !== false) {
                $maybeHtml = $decoded;
            }
        }

        $points = [];
        if (strpos($maybeHtml, '<') !== false) {
            try {
                $doc = new \DOMDocument('1.0', 'UTF-8');
                libxml_use_internal_errors(true);
                $doc->loadHTML('<?xml encoding="UTF-8"><body>' . $maybeHtml . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                libxml_clear_errors();

                foreach ($doc->getElementsByTagName('li') as $li) {
                    $text = trim(bms_description_node_text($li));
                    if ($text !== '') {
                        $points[] = $text;
                    }
                }
            } catch (\Throwable $e) {
                $points = [];
            }
        }

        if ($points === []) {
            $plain = bms_description_to_plain($value);
            if ($plain !== '') {
                $points = array_values(array_filter(array_map(
                    static function ($line) {
                        $line = trim((string) $line);
                        $line = preg_replace('/^[\\x{2022}\\x{2023}\\x{25E6}\\x{2043}\\x{2219}\\*\\-\\+]+\\s*/u', '', $line) ?? $line;
                        return trim((string) $line);
                    },
                    preg_split('/\\r?\\n/', $plain) ?: []
                ), static function ($line) {
                    return $line !== '';
                }));
            }
        }

        return $points;
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
        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

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

        if ($startDate !== '') {
            $builder->where('billable_items.entry_date >=', $startDate);
        }

        if ($endDate !== '') {
            $builder->where('billable_items.entry_date <=', $endDate);
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

    public function download()
    {
        $clientId = (int) $this->request->getGet('client_id');
        $status = trim((string) $this->request->getGet('status'));
        $monthParam = trim((string) $this->request->getGet('month'));
        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

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

        if ($startDate !== '') {
            $builder->where('billable_items.entry_date >=', $startDate);
        }

        if ($endDate !== '') {
            $builder->where('billable_items.entry_date <=', $endDate);
        }

        $rows = $builder->findAll();

        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, ['Entry No', 'Date', 'Client', 'Description', 'Billing Month', 'Currency', 'Quantity', 'Unit Price', 'Amount', 'Status', 'Proforma No']);
        foreach ($rows as $row) {
            $entryNo = (string) ($row['entry_no'] ?? ('BI-' . str_pad((string) ($row['id'] ?? ''), 5, '0', STR_PAD_LEFT)));
            $proformaNo = '';
            if (!empty($row['proforma_id'])) {
                $pm = new \App\Models\ProformaModel();
                $pRow = $pm->select('proforma_number')->find((int) $row['proforma_id']);
                $proformaNo = (string) ($pRow['proforma_number'] ?? '');
            }
            $entryDate = (string) ($row['entry_date'] ?? '');
            if ($entryDate !== '') {
                $dt = \DateTime::createFromFormat('Y-m-d', $entryDate);
                if ($dt) $entryDate = $dt->format('d/m/Y');
            }
            fputcsv($fh, [
                $entryNo,
                $entryDate,
                (string) ($row['client_name'] ?? ''),
                bms_description_to_plain((string) ($row['description'] ?? '')),
                (string) ($row['billing_month'] ?? ''),
                (string) ($row['currency'] ?? 'INR'),
                (string) ($row['quantity'] ?? ''),
                (string) ($row['unit_price'] ?? ''),
                (string) ($row['amount'] ?? ''),
                (string) ($row['status'] ?? ''),
                $proformaNo,
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh) ?: '';
        fclose($fh);

        $filename = 'billable-items-' . date('Y-m-d') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function pdf($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setBody('Invalid billable item.');
        }

        $billableItems = new BillableItemModel();
        $row = $billableItems
            ->select(
                "billable_items.*, 
                COALESCE(NULLIF(TRIM(clients.name),''), clients.contact_person, clients.email, CONCAT('Client #', clients.id)) as client_name,
                COALESCE(NULLIF(TRIM(clients.contact_person),''), NULLIF(TRIM(clients.name),''), clients.email, CONCAT('Client #', clients.id)) as client_contact_person,
                clients.gst_no as client_gst_no,
                COALESCE(NULLIF(TRIM(clients.billing_address),''), NULLIF(TRIM(clients.address),'')) as client_address,
                COALESCE(NULLIF(TRIM(clients.billing_city),''), NULLIF(TRIM(clients.city),'')) as client_city,
                COALESCE(NULLIF(TRIM(clients.billing_state),''), NULLIF(TRIM(clients.state),'')) as client_state,
                COALESCE(NULLIF(TRIM(clients.billing_country),''), NULLIF(TRIM(clients.country),'')) as client_country,
                COALESCE(NULLIF(TRIM(clients.billing_postal_code),''), NULLIF(TRIM(clients.postal_code),'')) as client_postal_code,
                clients.phone as client_phone",
                false
            )
            ->join('clients', 'clients.id = billable_items.client_id', 'left')
            ->find($id);

        if (! $row) {
            return $this->response->setStatusCode(404)->setBody('Billable item not found.');
        }

        $entryNo = trim((string) ($row['entry_no'] ?? '')) ?: $this->formatEntryNo($id);
        $entryDate = $this->formatPdfDate($row['entry_date'] ?? null);
        $clientName = trim((string) ($row['client_name'] ?? '')) ?: '-';
        $billToName = trim((string) ($row['client_contact_person'] ?? '')) ?: $clientName;
        $billToGst = trim((string) ($row['client_gst_no'] ?? ''));
        $billToAddress = trim((string) ($row['client_address'] ?? ''));
        $billToCity = trim((string) ($row['client_city'] ?? ''));
        $billToState = trim((string) ($row['client_state'] ?? ''));
        $billToCountry = trim((string) ($row['client_country'] ?? ''));
        $billToPostal = trim((string) ($row['client_postal_code'] ?? ''));
        $billToPhone = trim((string) ($row['client_phone'] ?? ''));
        $descriptionPoints = $this->extractDescriptionPoints($row['description'] ?? null);
        $billingMonth = $this->formatPdfMonth($row['billing_month'] ?? null);
        $currency = trim((string) ($row['currency'] ?? '')) ?: 'INR';
        $status = trim((string) ($row['status'] ?? '')) ?: '-';
        $companyInfo = function_exists('bms_company_info') ? bms_company_info() : [];
        $companyName = trim((string) ($companyInfo['company_name'] ?? '')) ?: 'Company Information';
        $companyAddress1 = trim((string) ($companyInfo['address_line1'] ?? ''));
        $companyAddress2 = trim((string) ($companyInfo['address_line2'] ?? ''));
        $companyCity = trim((string) ($companyInfo['city'] ?? ''));
        $companyState = trim((string) ($companyInfo['state'] ?? ''));
        $companyPincode = trim((string) ($companyInfo['pincode'] ?? ''));
        $companyWebsite = trim((string) ($companyInfo['website'] ?? ''));
        $companyWebsiteUrl = function_exists('bms_company_website_url')
            ? bms_company_website_url($companyWebsite)
            : (preg_match('#^https?://#i', $companyWebsite) === 1 ? $companyWebsite : ($companyWebsite !== '' ? 'https://' . $companyWebsite : ''));
        $companyEmail = trim((string) ($companyInfo['email_id'] ?? ''));
        $companyPhone = trim((string) ($companyInfo['phone_number'] ?? ''));
        $companyAccount = trim((string) ($companyInfo['current_account_details'] ?? ''));
        $companyPaypal = trim((string) ($companyInfo['paypal_account'] ?? ''));
        $companyLogoRel = trim((string) ($companyInfo['logo_path'] ?? ''));
        $companyLogoAbs = '';
        $logoCandidates = [];
        if ($companyLogoRel !== '') {
            $logoCandidates[] = $companyLogoRel;
        }
        $logoCandidates[] = 'assets/img/Netkathir_logo.png';
        foreach ($logoCandidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }
            $abs = realpath(rtrim(FCPATH, "\\/") . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($candidate, '/\\')));
            if ($abs && is_file($abs)) {
                $companyLogoAbs = $abs;
                break;
            }
        }

        $pdf = new SimplePdf();
        $pdf->addPage();
        $pageW = 595.28;
        $pageH = 841.89;
        $margin = 14.0;
        $xL = $margin;
        $xR = $pageW - $margin;
        $contentW = $xR - $xL;

        $baseAmount = (float) ($row['amount'] ?? 0);
        $cgstRate = 6.0;
        $sgstRate = 6.0;
        $cgstAmount = round($baseAmount * ($cgstRate / 100), 2);
        $sgstAmount = round($baseAmount * ($sgstRate / 100), 2);
        $totalAmount = round($baseAmount + $cgstAmount + $sgstAmount, 2);

        $moneyPrefix = strtoupper($currency) === 'INR'
            ? 'Rs. '
            : (trim($currency) !== '' ? ($currency . ' ') : '');
        $formatMoney = static function (float $value) use ($moneyPrefix): string {
            return $moneyPrefix . number_format($value, 2);
        };

        $companyNameUpper = function_exists('mb_strtoupper')
            ? mb_strtoupper($companyName, 'UTF-8')
            : strtoupper($companyName);

        $pdf->setDrawColor(160, 180, 186);
        $pdf->setFillColor(255, 255, 255);
        $pdf->setLineWidth(0.7);
        $pdf->rect($xL, 10.0, $contentW, 820.0, true, false);

        if ($companyLogoAbs !== '') {
            $pdf->image($companyLogoAbs, $xL + 20.0, 22.0, 78.0, 32.0);
        }

        $invoiceTitle = 'BILLABLE ITEM';
        $pdf->setFont('Helvetica', 'B', 20.0);
        $pdf->setTextColor(0, 0, 0);
        $pdf->text(($xL + $xR - $pdf->estimateTextWidth($invoiceTitle, 20.0)) / 2.0, 33.0, $invoiceTitle);

        $pdf->setDrawColor(146, 188, 194);
        $pdf->line($xL + 34.0, 64.0, $xR - 34.0, 64.0);

        $infoTop = 66.0;
        $infoH = 120.0;
        $leftInfoW = 292.0;
        $rightInfoX = $xL + $leftInfoW;

        $pdf->setDrawColor(150, 185, 191);
        $pdf->rect($xL + 22.0, $infoTop, $contentW - 44.0, $infoH, true, false);
        $pdf->line($rightInfoX, $infoTop, $rightInfoX, $infoTop + $infoH);

        $pdf->setFont('Helvetica', 'B', 11.2);
        $pdf->setTextColor(15, 118, 110);
        $pdf->text($xL + 34.0, $infoTop + 18.0, $companyNameUpper);

        $placeParts = array_values(array_filter([$companyCity, $companyState]));
        $companyLines = array_values(array_filter([
            $companyAddress1,
            $companyAddress2,
            trim(implode(', ', $placeParts)) . ($companyPincode !== '' ? ' - ' . $companyPincode : ''),
            $companyWebsite !== '' ? $companyWebsite : '',
            $companyEmail !== '' ? $companyEmail : '',
            $companyPhone !== '' ? $companyPhone : '',
        ], static fn ($line) => trim((string) $line) !== ''));

        $pdf->setFont('Helvetica', '', 8.5);
        $companyY = $infoTop + 31.0;
        foreach ($companyLines as $idx => $line) {
            $isLinkLine = $idx >= 4;
            $pdf->setTextColor($isLinkLine ? 25 : 30, $isLinkLine ? 92 : 41, $isLinkLine ? 204 : 59);
            foreach ($pdf->wrapText((string) $line, 238.0, 8.5) as $wrappedLine) {
                $pdf->text($xL + 34.0, $companyY, $wrappedLine);
                $companyY += 10.5;
            }
        }

        $metaRows = [
            ['Entry No:', $entryNo],
            ['Entry Date:', $entryDate],
            ['Client:', $billToName],
            ['Billing Month:', $billingMonth !== '' ? $billingMonth : '-'],
            ['Status:', $status],
        ];

        $labelX = $rightInfoX + 12.0;
        $valueX = $labelX + 74.0;
        $metaY = $infoTop + 18.0;
        foreach ($metaRows as [$label, $value]) {
            $pdf->setFont('Helvetica', 'B', 8.6);
            $pdf->setTextColor(15, 23, 42);
            $pdf->text($labelX, $metaY, $label);

            $pdf->setFont('Helvetica', '', 8.6);
            $pdf->setTextColor(30, 41, 59);
            $lines = preg_split('/\r?\n/', (string) $value) ?: ['-'];
            if ($lines === []) {
                $lines = ['-'];
            }
            $valueY = $metaY;
            foreach ($lines as $line) {
                $wrappedLines = $pdf->wrapText(trim((string) $line), 128.0, 8.6);
                foreach ($wrappedLines as $wrappedLine) {
                    $pdf->text($valueX, $valueY, $wrappedLine);
                    $valueY += 10.5;
                }
            }
            $metaY += max(17.0, $valueY - $metaY + 1.0);
        }

        $tableTop = $infoTop + $infoH + 12.0;
        $tableW = $contentW - 44.0;
        $tableX = $xL + 22.0;
        $wDesc = 292.0;
        $wUnit = 50.0;
        $wPrice = 66.0;
        $wQty = 54.0;
        $wAmt = $tableW - ($wDesc + $wUnit + $wPrice + $wQty);
        $tableHeadH = 22.0;

        $pdf->setDrawColor(150, 185, 191);
        $pdf->setFillColor(240, 245, 246);
        $pdf->rect($tableX, $tableTop, $tableW, $tableHeadH, true, true);
        $pdf->setFont('Helvetica', 'B', 9.0);
        $pdf->setTextColor(15, 23, 42);
        $pdf->text($tableX + 8.0, $tableTop + 14.0, 'Description');
        $pdf->text($tableX + $wDesc + 11.0, $tableTop + 14.0, 'Unit');
        $pdf->text($tableX + $wDesc + $wUnit + 9.0, $tableTop + 14.0, 'Price');
        $pdf->text($tableX + $wDesc + $wUnit + $wPrice + 8.0, $tableTop + 14.0, 'Quantity');
        $pdf->text($tableX + $wDesc + $wUnit + $wPrice + $wQty + 8.0, $tableTop + 14.0, 'Amount');

        $points = $descriptionPoints;
        if ($points === []) {
            $points = ['-'];
        }
        $descLines = [];
        foreach ($points as $point) {
            $lineText = trim((string) $point);
            $lineText = preg_replace('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}\*\-\+]+\s*/u', '', $lineText) ?? $lineText;
            foreach ($pdf->wrapText('- ' . $lineText, $wDesc - 18.0, 8.8) as $wrappedLine) {
                $descLines[] = $wrappedLine;
            }
        }
        if ($descLines === []) {
            $descLines = ['-'];
        }

        $tableRowTop = $tableTop + $tableHeadH;
        $rowH = max(28.0, 12.0 + (count($descLines) * 10.5));
        $pdf->setDrawColor(150, 185, 191);
        $pdf->setFillColor(255, 255, 255);
        $pdf->rect($tableX, $tableRowTop, $tableW, $rowH, true, true);
        $pdf->rect($tableX, $tableRowTop, $wDesc, $rowH, true, true);
        $pdf->rect($tableX + $wDesc, $tableRowTop, $wUnit, $rowH, true, true);
        $pdf->rect($tableX + $wDesc + $wUnit, $tableRowTop, $wPrice, $rowH, true, true);
        $pdf->rect($tableX + $wDesc + $wUnit + $wPrice, $tableRowTop, $wQty, $rowH, true, true);
        $pdf->rect($tableX + $wDesc + $wUnit + $wPrice + $wQty, $tableRowTop, $wAmt, $rowH, true, true);

        $pdf->setFont('Helvetica', '', 8.8);
        $pdf->setTextColor(30, 41, 59);
        $descY = $tableRowTop + 14.0;
        foreach ($descLines as $descLine) {
            $pdf->text($tableX + 8.0, $descY, $descLine);
            $descY += 10.5;
        }

        $unitText = 'Nos';
        $qtyText = rtrim(rtrim(number_format((float) ($row['quantity'] ?? 0), 2, '.', ''), '0'), '.');
        if ($qtyText === '') {
            $qtyText = '0';
        }
        $priceText = $formatMoney((float) ($row['unit_price'] ?? 0));
        $amtText = $formatMoney($baseAmount);
        $midY = $tableRowTop + ($rowH / 2.0) + 2.0;

        $pdf->text($tableX + $wDesc + 14.0, $midY, $unitText);
        $pdf->text($tableX + $wDesc + $wUnit + $wPrice - 8.0 - $pdf->estimateTextWidth($priceText, 8.8), $midY, $priceText);
        $pdf->text($tableX + $wDesc + $wUnit + $wPrice + $wQty - 8.0 - $pdf->estimateTextWidth($qtyText, 8.8), $midY, $qtyText);
        $pdf->text($tableX + $wDesc + $wUnit + $wPrice + $wQty + $wAmt - 8.0 - $pdf->estimateTextWidth($amtText, 8.8), $midY, $amtText);

        $summaryW = 244.0;
        $summaryX = $xR - $summaryW - 10.0;
        $summaryTop = $tableRowTop + $rowH + 10.0;
        $summaryRows = [
            ['Invoice Total', $formatMoney($baseAmount)],
            ['Total Amount', $formatMoney($baseAmount)],
        ];
        $sumRowH = 23.0;
        $pdf->setDrawColor(150, 185, 191);
        $pdf->setFillColor(255, 255, 255);
        $pdf->rect($summaryX, $summaryTop, $summaryW, $sumRowH * count($summaryRows), true, true);
        foreach ($summaryRows as $i => [$label, $value]) {
            $rowTop = $summaryTop + ($i * $sumRowH);
            $pdf->setFillColor(255, 255, 255);
            $pdf->rect($summaryX, $rowTop, $summaryW, $sumRowH, true, true);
            $pdf->setFont('Helvetica', 'B', 8.9);
            $pdf->setTextColor(15, 23, 42);
            $pdf->text($summaryX + 10.0, $rowTop + 14.0, $label);
            $valueX = $summaryX + $summaryW - 10.0 - $pdf->estimateTextWidth($value, 8.9);
            $pdf->text($valueX, $rowTop + 14.0, $value);
        }

        $footerTop = $summaryTop + ($sumRowH * count($summaryRows)) + 16.0;
        $pdf->setFont('Helvetica', '', 8.4);
        $pdf->setTextColor(30, 41, 59);
        $leftFooterY = $footerTop;
        if ($companyAccount !== '') {
            $pdf->text($xL + 34.0, $leftFooterY, $companyAccount);
            $leftFooterY += 10.5;
        }
        if ($companyPaypal !== '') {
            $pdf->text($xL + 34.0, $leftFooterY, 'Paypal account: ' . $companyPaypal);
            $leftFooterY += 10.5;
        }
        $supportLine = 'For support, contact ' . ($companyEmail !== '' ? $companyEmail : $companyName) . ($companyPhone !== '' ? (' / ' . $companyPhone) : '');
        $pdf->text($xL + 34.0, $leftFooterY, $supportLine);
        $leftFooterY += 10.5;

        $pdf->setFont('Helvetica', 'B', 8.8);
        $pdf->setTextColor(30, 41, 59);
        $rightFooterX = $xR - 190.0;
        $pdf->text($rightFooterX, $footerTop + 8.0, 'For ' . $companyName);
        $pdf->setFont('Helvetica', '', 7.8);
        $pdf->text($rightFooterX - 6.0, $footerTop + 19.0, 'This is a computer-generated bill and does not require a');
        $pdf->text($rightFooterX - 6.0, $footerTop + 29.0, 'signature.');

        $thankYou = 'Thank you for your business.!';
        $pdf->setFont('Helvetica', 'B', 8.8);
        $pdf->setTextColor(30, 41, 59);
        $pdf->text(($xL + $xR - $pdf->estimateTextWidth($thankYou, 8.8)) / 2.0, min($pageH - 34.0, $footerTop + 40.0), $thankYou);

        $filename = 'billable-item-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $entryNo) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdf->output());
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


