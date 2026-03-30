<?php

namespace App\Libraries;

use App\Models\BillableItemModel;
use App\Models\ClientModel;
use App\Models\ProformaItemModel;
use App\Models\ProformaModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;
use Throwable;

class ProformaService
{
    public function __construct(
        private readonly BaseConnection $db,
        private readonly BillableItemModel $billableItems,
        private readonly ProformaModel $proformas,
        private readonly ProformaItemModel $proformaItems,
    ) {
    }

    /**
     * @param list<int> $billableItemIds
     * @param array{proforma_number?:string,proforma_date?:string,billing_from?:string|null,billing_to?:string|null,currency?:string,status?:string,invoice_type?:string,gst_percent?:string|float,gst_mode?:string} $meta
     * @return array{id:int,proforma_number:string,total_amount:string}
     */
    public function create(int $clientId, array $billableItemIds, array $meta = []): array
    {
        $billableItemIds = array_values(array_unique(array_map('intval', $billableItemIds)));
        if ($clientId <= 0 || $billableItemIds === []) {
            throw new RuntimeException('Please select at least one billable item.');
        }

        $client = (new ClientModel())->find($clientId);
        if (! $client) {
            throw new RuntimeException('Client not found.');
        }

        $clientCountry = trim((string) ($client['country'] ?? ''));
        $isIndiaClient = function_exists('bms_is_india_country')
            ? bms_is_india_country($clientCountry)
            : in_array(strtolower($clientCountry), ['', 'india', 'in'], true);

        $proformaDate = isset($meta['proforma_date']) && $meta['proforma_date'] !== ''
            ? (string) $meta['proforma_date']
            : date('Y-m-d');

        $status = isset($meta['status']) && $meta['status'] !== ''
            ? (string) $meta['status']
            : ProformaModel::STATUS_DRAFT;

        $invoiceType = $isIndiaClient ? ProformaModel::TYPE_GST : ProformaModel::TYPE_EXPORT;

        $billingFrom = $meta['billing_from'] ?? null;
        $billingTo = $meta['billing_to'] ?? null;
        $currency = isset($meta['currency']) && $meta['currency'] !== '' ? (string) $meta['currency'] : 'INR';

        $gstPercent = isset($meta['gst_percent']) && $meta['gst_percent'] !== '' ? (float) $meta['gst_percent'] : 0.0;
        $gstMode = isset($meta['gst_mode']) && $meta['gst_mode'] !== '' ? (string) $meta['gst_mode'] : ProformaModel::GST_MODE_CGST_SGST;

        $this->db->transBegin();

        try {
            $items = $this->billableItems
                ->select('id, client_id, amount, status, proforma_id, invoice_id')
                ->whereIn('id', $billableItemIds)
                ->where('client_id', $clientId)
                ->findAll();

            if (count($items) !== count($billableItemIds)) {
                throw new RuntimeException('Some selected billable items are missing or belong to another client.');
            }

            foreach ($items as $item) {
                if (($item['status'] ?? null) !== BillableItemModel::STATUS_PENDING) {
                    throw new RuntimeException('Only "Pending" items can be added.');
                }
                if (! empty($item['proforma_id']) || ! empty($item['invoice_id'])) {
                    throw new RuntimeException('Selected billable items are already linked to a proforma/invoice.');
                }
            }

            $total = 0.0;
            foreach ($items as $item) {
                $total += (float) ($item['amount'] ?? 0);
            }

            $cgst = 0.0;
            $sgst = 0.0;
            $igst = 0.0;
            $totalGst = 0.0;
            $netAmount = $total;

            if ($invoiceType === ProformaModel::TYPE_GST && $gstPercent > 0) {
                $tax = ($total * $gstPercent) / 100.0;
                if ($gstMode === ProformaModel::GST_MODE_IGST) {
                    $igst = $tax;
                } else {
                    $cgst = $tax / 2.0;
                    $sgst = $tax / 2.0;
                }
                $totalGst = $cgst + $sgst + $igst;
                $netAmount = $total + $totalGst;
            }

            $proformaId = null;
            $proformaNumber = null;
            $customNumber = trim((string) ($meta['proforma_number'] ?? ''));

            for ($i = 0; $i < 3; $i++) {
                $proformaNumber = $customNumber !== '' ? $customNumber : $this->proformas->nextProformaNumber($proformaDate);

                $proformaId = $this->proformas->insert([
                    'proforma_number' => $proformaNumber,
                    'client_id'       => $clientId,
                    'proforma_date'   => $proformaDate,
                    'invoice_type'    => $invoiceType,
                    'billing_from'    => $billingFrom ?: null,
                    'billing_to'      => $billingTo ?: null,
                    'currency'        => $currency,
                    'gst_percent'     => $invoiceType === ProformaModel::TYPE_GST ? number_format($gstPercent, 2, '.', '') : null,
                    'gst_mode'        => $invoiceType === ProformaModel::TYPE_GST ? $gstMode : null,
                    'cgst_amount'     => $invoiceType === ProformaModel::TYPE_GST ? number_format($cgst, 2, '.', '') : null,
                    'sgst_amount'     => $invoiceType === ProformaModel::TYPE_GST ? number_format($sgst, 2, '.', '') : null,
                    'igst_amount'     => $invoiceType === ProformaModel::TYPE_GST ? number_format($igst, 2, '.', '') : null,
                    'total_gst'       => $invoiceType === ProformaModel::TYPE_GST ? number_format($totalGst, 2, '.', '') : null,
                    'net_amount'      => number_format($netAmount, 2, '.', ''),
                    'total_amount'    => number_format($total, 2, '.', ''),
                    'status'          => $status,
                ], true);

                if ($proformaId) {
                    break;
                }

                if ($customNumber !== '') {
                    $err = $this->proformas->db ? (array) $this->proformas->db->error() : [];
                    $code = (int) ($err['code'] ?? 0);
                    if ($code === 1062) {
                        throw new RuntimeException('Invoice No already exists.');
                    }
                    throw new RuntimeException('Unable to create invoice with the given Invoice No.');
                }
            }

            if (! $proformaId || ! $proformaNumber) {
                throw new RuntimeException('Unable to create proforma invoice. Please retry.');
            }

            $rows = [];
            foreach ($items as $item) {
                $rows[] = [
                    'proforma_id'      => $proformaId,
                    'billable_item_id' => (int) $item['id'],
                    'amount'           => (string) $item['amount'],
                ];
            }
            $this->proformaItems->insertBatch($rows);

            $this->billableItems
                ->whereIn('id', $billableItemIds)
                ->set([
                    'status'      => BillableItemModel::STATUS_BILLED,
                    'proforma_id' => $proformaId,
                ])
                ->update();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Database error while creating proforma invoice.');
            }

            $this->db->transCommit();

            return [
                'id'             => (int) $proformaId,
                'proforma_number'=> (string) $proformaNumber,
                'total_amount'   => number_format($total, 2, '.', ''),
                'net_amount'     => number_format($netAmount, 2, '.', ''),
            ];
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * @param list<int> $billableItemIds
     * @param array{proforma_number?:string,proforma_date?:string,billing_from?:string|null,billing_to?:string|null,currency?:string,status?:string,invoice_type?:string,gst_percent?:string|float,gst_mode?:string} $meta
     * @return array{id:int,proforma_number:string,total_amount:string}
     */
    public function update(int $proformaId, array $billableItemIds, array $meta = []): array
    {
        $billableItemIds = array_values(array_unique(array_map('intval', $billableItemIds)));
        if ($proformaId <= 0 || $billableItemIds === []) {
            throw new RuntimeException('Please select at least one billable item.');
        }


        $proforma = $this->proformas->find($proformaId);
        if (! $proforma) {
            throw new RuntimeException('Proforma not found.');
        }

        $clientId = (int) ($proforma['client_id'] ?? 0);
        if ($clientId <= 0) {
            throw new RuntimeException('Invalid proforma client.');
        }

        $client = (new ClientModel())->find($clientId);
        if (! $client) {
            throw new RuntimeException('Client not found.');
        }

        $clientCountry = trim((string) ($client['country'] ?? ''));
        $isIndiaClient = function_exists('bms_is_india_country')
            ? bms_is_india_country($clientCountry)
            : in_array(strtolower($clientCountry), ['', 'india', 'in'], true);

        $proformaDate = isset($meta['proforma_date']) && $meta['proforma_date'] !== ''
            ? (string) $meta['proforma_date']
            : (string) ($proforma['proforma_date'] ?? date('Y-m-d'));

        $status = isset($meta['status']) && $meta['status'] !== ''
            ? (string) $meta['status']
            : (string) ($proforma['status'] ?? ProformaModel::STATUS_DRAFT);
        $invoiceType = $isIndiaClient ? ProformaModel::TYPE_GST : ProformaModel::TYPE_EXPORT;

        $billingFrom = $meta['billing_from'] ?? ($proforma['billing_from'] ?? null);
        $billingTo = $meta['billing_to'] ?? ($proforma['billing_to'] ?? null);
        $currency = isset($meta['currency']) && $meta['currency'] !== ''
            ? (string) $meta['currency']
            : (string) ($proforma['currency'] ?? 'INR');

        $gstPercent = isset($meta['gst_percent']) && $meta['gst_percent'] !== '' ? (float) $meta['gst_percent'] : (float) ($proforma['gst_percent'] ?? 0);
        $gstMode = isset($meta['gst_mode']) && $meta['gst_mode'] !== '' ? (string) $meta['gst_mode'] : (string) ($proforma['gst_mode'] ?? ProformaModel::GST_MODE_CGST_SGST);

	        $this->db->transBegin();

	        try {
	            $customNumber = trim((string) ($meta['proforma_number'] ?? ''));
	            $currentNumber = (string) ($proforma['proforma_number'] ?? '');
	            if ($customNumber !== '' && $customNumber !== $currentNumber) {
	                $exists = $this->proformas
	                    ->where('proforma_number', $customNumber)
	                    ->where('id !=', $proformaId)
	                    ->countAllResults();
	                if ($exists > 0) {
	                    throw new RuntimeException('Invoice No already exists.');
	                }
	            }

	            $currentRows = $this->proformaItems
	                ->select('billable_item_id')
	                ->where('proforma_id', $proformaId)
	                ->findAll();

            $currentIds = array_values(array_filter(array_map(
                static fn (array $r): int => (int) ($r['billable_item_id'] ?? 0),
                $currentRows
            )));

            $currentSet = array_fill_keys($currentIds, true);
            $newSet = array_fill_keys($billableItemIds, true);

            $toRemove = array_values(array_diff($currentIds, $billableItemIds));
            $toAdd = array_values(array_diff($billableItemIds, $currentIds));

            // Validate additions (must be pending and unassigned, same client)
            if ($toAdd !== []) {
                $addItems = $this->billableItems
                    ->select('id, client_id, amount, status, proforma_id, invoice_id')
                    ->whereIn('id', $toAdd)
                    ->where('client_id', $clientId)
                    ->findAll();

                if (count($addItems) !== count($toAdd)) {
                    throw new RuntimeException('Some selected billable items are missing or belong to another client.');
                }

                foreach ($addItems as $item) {
                    if (($item['status'] ?? null) !== BillableItemModel::STATUS_PENDING) {
                        throw new RuntimeException('Only "Pending" items can be added.');
                    }
                    if (! empty($item['proforma_id']) || ! empty($item['invoice_id'])) {
                        throw new RuntimeException('Selected billable items are already linked to a proforma/invoice.');
                    }
                }

                $rows = [];
                foreach ($addItems as $item) {
                    $rows[] = [
                        'proforma_id'      => $proformaId,
                        'billable_item_id' => (int) $item['id'],
                        'amount'           => (string) $item['amount'],
                    ];
                }
                $this->proformaItems->insertBatch($rows);

                $this->billableItems
                    ->whereIn('id', $toAdd)
                    ->set([
                        'status'      => BillableItemModel::STATUS_BILLED,
                        'proforma_id' => $proformaId,
                    ])
                    ->update();
            }

            // Remove deselected items
            if ($toRemove !== []) {
                $removeItems = $this->billableItems
                    ->select('id, invoice_id')
                    ->whereIn('id', $toRemove)
                    ->where('proforma_id', $proformaId)
                    ->findAll();

                foreach ($removeItems as $item) {
                    if (! empty($item['invoice_id'])) {
                        throw new RuntimeException('Cannot remove items that are already invoiced.');
                    }
                }

                $this->proformaItems
                    ->where('proforma_id', $proformaId)
                    ->whereIn('billable_item_id', $toRemove)
                    ->delete();

                $this->billableItems
                    ->whereIn('id', $toRemove)
                    ->where('proforma_id', $proformaId)
                    ->set([
                        'status'      => BillableItemModel::STATUS_PENDING,
                        'proforma_id' => null,
                    ])
                    ->update();
            }

            // Recalculate total from resulting selection
            $allSelected = array_keys($newSet);
            $selectedItems = $this->billableItems
                ->select('id, amount')
                ->whereIn('id', $allSelected)
                ->where('client_id', $clientId)
                ->findAll();

            if (count($selectedItems) !== count($allSelected)) {
                throw new RuntimeException('Some selected billable items are invalid.');
            }

            $total = 0.0;
            $amountMap = [];
            foreach ($selectedItems as $item) {
                $amt = (float) ($item['amount'] ?? 0);
                $total += $amt;
                $amountMap[(int) ($item['id'] ?? 0)] = number_format($amt, 2, '.', '');
            }

            // Keep proforma_items.amount in sync with billable_items.amount.
            if ($amountMap !== []) {
                $piRows = $this->proformaItems
                    ->select('id, billable_item_id')
                    ->where('proforma_id', $proformaId)
                    ->whereIn('billable_item_id', array_keys($amountMap))
                    ->findAll();

                $updates = [];
                foreach ($piRows as $r) {
                    $bid = (int) ($r['billable_item_id'] ?? 0);
                    $pid = (int) ($r['id'] ?? 0);
                    if ($pid <= 0 || $bid <= 0) {
                        continue;
                    }
                    if (! isset($amountMap[$bid])) {
                        continue;
                    }
                    $updates[] = [
                        'id'     => $pid,
                        'amount' => $amountMap[$bid],
                    ];
                }

                if ($updates !== []) {
                    $this->proformaItems->updateBatch($updates, 'id');
                }
            }

            $cgst = 0.0;
            $sgst = 0.0;
            $igst = 0.0;
            $totalGst = 0.0;
            $netAmount = $total;

            if ($invoiceType === ProformaModel::TYPE_GST && $gstPercent > 0) {
                $tax = ($total * $gstPercent) / 100.0;
                if ($gstMode === ProformaModel::GST_MODE_IGST) {
                    $igst = $tax;
                } else {
                    $cgst = $tax / 2.0;
                    $sgst = $tax / 2.0;
                }
                $totalGst = $cgst + $sgst + $igst;
                $netAmount = $total + $totalGst;
            }

	            $updatePayload = [
	                'proforma_date' => $proformaDate,
	                'invoice_type'  => $invoiceType,
	                'billing_from'  => $billingFrom ?: null,
	                'billing_to'    => $billingTo ?: null,
	                'currency'      => $currency,
	                'gst_percent'   => $invoiceType === ProformaModel::TYPE_GST ? number_format($gstPercent, 2, '.', '') : null,
	                'gst_mode'      => $invoiceType === ProformaModel::TYPE_GST ? $gstMode : null,
	                'cgst_amount'   => $invoiceType === ProformaModel::TYPE_GST ? number_format($cgst, 2, '.', '') : null,
	                'sgst_amount'   => $invoiceType === ProformaModel::TYPE_GST ? number_format($sgst, 2, '.', '') : null,
	                'igst_amount'   => $invoiceType === ProformaModel::TYPE_GST ? number_format($igst, 2, '.', '') : null,
	                'total_gst'     => $invoiceType === ProformaModel::TYPE_GST ? number_format($totalGst, 2, '.', '') : null,
	                'net_amount'    => number_format($netAmount, 2, '.', ''),
	                'total_amount'  => number_format($total, 2, '.', ''),
	                'status'        => $status,
	            ];

	            if ($customNumber !== '') {
	                $updatePayload['proforma_number'] = $customNumber;
	            }

	            $this->proformas->update($proformaId, $updatePayload);

	            if ($this->db->transStatus() === false) {
	                throw new RuntimeException('Database error while updating proforma invoice.');
	            }

            $this->db->transCommit();

	            return [
	                'id'              => (int) $proformaId,
	                'proforma_number' => $customNumber !== '' ? $customNumber : (string) ($proforma['proforma_number'] ?? ''),
	                'total_amount'    => number_format($total, 2, '.', ''),
	                'net_amount'      => number_format($netAmount, 2, '.', ''),
	            ];
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}



