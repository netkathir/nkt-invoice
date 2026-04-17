<?php
    $mode = isset($mode) ? (string) $mode : 'create';
    $isEdit = $mode === 'edit';
    $isView = $mode === 'view';
    $isCreate = $mode === 'create';
    $isReadonly = $isView;

    $proforma = isset($proforma) && is_array($proforma) ? $proforma : [];
    $clients = isset($clients) && is_array($clients) ? $clients : [];
    $items = isset($items) && is_array($items) ? $items : [];

    $formatDate = static function ($value): string {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        $ts = strtotime($raw);
        if ($ts === false) {
            return $raw;
        }

        return date('d/m/Y', $ts);
    };

    $formatPercent = static function (float $value): string {
        $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        return ($formatted === '' ? '0' : $formatted) . '%';
    };

    $buildClientLabel = static function (array $client): string {
        return (string) ($client['name'] ?: ($client['contact_person'] ?: ($client['email'] ?: ($client['phone'] ?: ('Client #' . ($client['id'] ?? ''))))));
    };

    $title = $isCreate ? 'Create Invoice' : ($isEdit ? 'Edit Invoice' : 'View Invoice');
    $invoiceNo = $isCreate ? (string) ($nextInvoiceNo ?? '') : (string) ($proforma['proforma_number'] ?? '');
    $selectedClientId = (string) ($proforma['client_id'] ?? '');
    $issueDate = $isCreate ? date('d/m/Y') : $formatDate($proforma['proforma_date'] ?? '');
    $dueDate = $isCreate ? date('d/m/Y', strtotime('+30 days')) : $formatDate($proforma['billing_to'] ?? '');
    $billingFrom = (string) ($proforma['billing_from'] ?? '');
    $currency = (string) (($proforma['currency'] ?? '') ?: 'INR');
    $invoiceType = (string) (($proforma['invoice_type'] ?? '') ?: 'GST Invoice');
    $gstPercent = (float) (($proforma['gst_percent'] ?? null) ?? ($isCreate ? 18 : 0));
    $gstMode = (string) (($proforma['gst_mode'] ?? '') ?: 'CGST_SGST');
    $companyInfo = function_exists('bms_company_info') ? bms_company_info() : [];
    $totalAmount = (float) ($proforma['total_amount'] ?? 0);
    $totalGst = (float) ($proforma['total_gst'] ?? 0);
    $netAmount = (float) (($proforma['net_amount'] ?? null) ?? ($totalAmount + $totalGst));
    $selectedClient = null;

    if ($isView && $clients === []) {
        $clients = [[
            'id' => $selectedClientId,
            'name' => (string) ($proforma['client_name'] ?? ''),
            'contact_person' => (string) ($proforma['contact_person'] ?? ''),
            'email' => (string) ($proforma['email'] ?? ''),
            'phone' => (string) ($proforma['phone'] ?? ''),
            'gst_no' => (string) ($proforma['gst_no'] ?? ''),
            'address' => (string) ($proforma['address'] ?? ''),
            'billing_address' => (string) ($proforma['billing_address'] ?? ''),
            'billing_city' => (string) ($proforma['billing_city'] ?? ''),
            'billing_state' => (string) ($proforma['billing_state'] ?? ''),
            'billing_country' => (string) ($proforma['billing_country'] ?? ''),
            'billing_postal_code' => (string) ($proforma['billing_postal_code'] ?? ''),
            'city' => (string) ($proforma['city'] ?? ''),
            'state' => (string) ($proforma['state'] ?? ''),
            'country' => (string) ($proforma['country'] ?? ''),
            'postal_code' => (string) ($proforma['postal_code'] ?? ''),
        ]];
    }

    foreach ($clients as $client) {
        if ((string) ($client['id'] ?? '') === $selectedClientId) {
            $selectedClient = $client;
            break;
        }
    }

    $gstMode = function_exists('bms_resolve_gst_mode')
        ? bms_resolve_gst_mode($selectedClient ?: $proforma, $companyInfo, $gstMode)
        : $gstMode;
    $cgstRate = $invoiceType === 'GST Invoice' && $gstMode !== 'IGST' ? ($gstPercent / 2) : 0;
    $sgstRate = $invoiceType === 'GST Invoice' && $gstMode !== 'IGST' ? ($gstPercent / 2) : 0;
    $igstRate = $invoiceType === 'GST Invoice' && $gstMode === 'IGST' ? $gstPercent : 0;
    $cgstAmount = (float) ($proforma['cgst_amount'] ?? ($gstMode === 'IGST' ? 0 : ($totalGst / 2)));
    $sgstAmount = (float) ($proforma['sgst_amount'] ?? ($gstMode === 'IGST' ? 0 : ($totalGst / 2)));
    $igstAmount = (float) ($proforma['igst_amount'] ?? ($gstMode === 'IGST' ? $totalGst : 0));

    $initialCompany = (string) (($selectedClient['name'] ?? '') ?: ($proforma['client_name'] ?? ''));
    $initialGstNo = (string) (($selectedClient['gst_no'] ?? '') ?: ($proforma['gst_no'] ?? ''));
    $initialAddr1 = (string) (($selectedClient['address'] ?? '') ?: ($proforma['address'] ?? ''));
    $initialAddr2 = (string) (($selectedClient['billing_address'] ?? '') ?: ($proforma['billing_address'] ?? ''));
    $initialCity = (string) ((($selectedClient['billing_city'] ?? '') ?: ($selectedClient['city'] ?? '')) ?: (($proforma['billing_city'] ?? '') ?: ($proforma['city'] ?? '')));
    $initialState = (string) ((($selectedClient['billing_state'] ?? '') ?: ($selectedClient['state'] ?? '')) ?: (($proforma['billing_state'] ?? '') ?: ($proforma['state'] ?? '')));
    $initialPincode = (string) ((((($selectedClient['billing_postal_code'] ?? '') ?: ($selectedClient['postal_code'] ?? '')) ?: ($selectedClient['pincode'] ?? '')) ?: (($proforma['billing_postal_code'] ?? '') ?: ($proforma['postal_code'] ?? ''))));
    $showGstFields = $invoiceType === 'GST Invoice';
    $showSplitGstRows = $showGstFields && $gstMode !== 'IGST';
    $showIgstRow = $showGstFields && $gstMode === 'IGST';
    $fieldLabelColClass = 'col-12 col-md-3';
    $fieldInputColClass = 'col-12 col-md-9';
    $summaryLabelColClass = 'col-4 col-md-3 text-end fw-semibold';
    $summaryValueColClass = 'col-8 col-md-9';
?>
<div
    class="bms-invoice-create-page"
    data-default-gst-mode="CGST_SGST"
    data-initial-client-id="<?= esc($selectedClientId) ?>"
    data-initial-gst-mode="<?= esc($gstMode) ?>"
>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 bms-invoice-create-toolbar">
        <h5 class="mb-0"><?= esc($title) ?></h5>
        <a class="btn btn-link p-0 text-decoration-none" href="<?= base_url('proforma') ?>">Back</a>
    </div>

    <div class="card bms-invoice-card bms-invoice-create-card">
        <div class="card-body">
            <?php if ($isEdit): ?>
                <input type="hidden" id="pf_id" value="<?= esc((string) ($proforma['id'] ?? '')) ?>">
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="row g-3 align-items-center">
                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Invoice No <span class="text-danger">*</span></label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <input
                                type="text"
                                class="form-control"
                                id="pf_invoice_no"
                                value="<?= esc($invoiceNo) ?>"
                                <?= $isCreate ? 'readonly' : '' ?>
                                <?= $isReadonly ? 'readonly' : '' ?>
                                required
                            >
                            <?php if (! $isReadonly): ?>
                                <div class="invalid-feedback">Invoice No is required.</div>
                            <?php endif; ?>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Client Name <span class="text-danger">*</span></label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <select class="form-select" id="pf_client_id" <?= $isReadonly ? 'disabled' : '' ?> required>
                                <option value="">Select client</option>
                                <?php foreach ($clients as $c): ?>
                                    <?php
                                        $clientLabel = $buildClientLabel($c);
                                        $company = (string) (($c['name'] ?? '') ?: $clientLabel);
                                        $gstNo = (string) (($c['gst_no'] ?? '') ?: ($c['gst'] ?? ''));
                                        $clientGstMode = function_exists('bms_resolve_gst_mode')
                                            ? bms_resolve_gst_mode($c, $companyInfo, 'CGST_SGST')
                                            : 'CGST_SGST';
                                        $selected = (string) ($c['id'] ?? '') === $selectedClientId;
                                    ?>
                                    <option
                                        value="<?= esc((string) ($c['id'] ?? '')) ?>"
                                        data-label="<?= esc($clientLabel) ?>"
                                        data-company="<?= esc($company) ?>"
                                        data-gst="<?= esc($gstNo) ?>"
                                        data-gst-mode="<?= esc($clientGstMode) ?>"
                                        data-addr1="<?= esc((string) ($c['address'] ?? '')) ?>"
                                        data-addr2="<?= esc((string) ($c['billing_address'] ?? '')) ?>"
                                        data-city="<?= esc((string) (($c['billing_city'] ?? '') ?: ($c['city'] ?? ''))) ?>"
                                        data-state="<?= esc((string) (($c['billing_state'] ?? '') ?: ($c['state'] ?? ''))) ?>"
                                        data-country="<?= esc((string) (($c['billing_country'] ?? '') ?: ($c['country'] ?? ''))) ?>"
                                        data-pincode="<?= esc((string) ((($c['billing_postal_code'] ?? '') ?: ($c['postal_code'] ?? '')) ?: ($c['pincode'] ?? ''))) ?>"
                                        <?= $selected ? 'selected' : '' ?>
                                    ><?= esc($clientLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (! $isReadonly): ?>
                                <div class="invalid-feedback">Client Name is required.</div>
                            <?php endif; ?>
                            <input type="hidden" id="pf_from" value="<?= esc($billingFrom) ?>">
                        </div>

                        <div class="<?= $fieldLabelColClass . ($showGstFields ? '' : ' d-none') ?>" id="pf_gst_row">
                            <label class="form-label mb-0 text-md-end fw-semibold">GST NO</label>
                        </div>
                        <div class="<?= $fieldInputColClass . ($showGstFields ? '' : ' d-none') ?>" id="pf_gst_col">
                            <input type="text" class="form-control" id="pf_gst" value="<?= esc($initialGstNo) ?>" readonly>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Street Name</label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <input type="text" class="form-control" id="pf_addr1" value="<?= esc($initialAddr1) ?>" <?= $isReadonly ? 'readonly' : '' ?>>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">State</label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <input type="text" class="form-control" id="pf_state" value="<?= esc($initialState) ?>" <?= $isReadonly ? 'readonly' : '' ?>>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Currency <span class="text-danger">*</span></label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <select class="form-select" id="pf_currency" <?= $isReadonly ? 'disabled' : '' ?> required>
                                <option value="">Select</option>
                                <option value="INR" <?= $currency === 'INR' ? 'selected' : '' ?>>INR</option>
                                <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD</option>
                                <option value="EUR" <?= $currency === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                <option value="GBP" <?= $currency === 'GBP' ? 'selected' : '' ?>>GBP</option>
                            </select>
                            <?php if (! $isReadonly): ?>
                                <div class="invalid-feedback">Currency is required.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="row g-3 align-items-center">
                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <select class="form-select" id="pf_invoice_type" required disabled>
                                <option value="GST Invoice" <?= $invoiceType === 'GST Invoice' ? 'selected' : '' ?>>GST Invoice</option>
                                <option value="Export Invoice" <?= $invoiceType === 'Export Invoice' ? 'selected' : '' ?>>Export Invoice</option>
                            </select>
                            <div class="form-text">Auto-selected from client country.</div>
                            <?php if (! $isReadonly): ?>
                                <div class="invalid-feedback">Invoice Type is required.</div>
                            <?php endif; ?>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Company Name</label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <input type="text" class="form-control" id="pf_company" value="<?= esc($initialCompany) ?>" readonly>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Date Of Issue <span class="text-danger">*</span></label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <?php if ($isReadonly): ?>
                                <input type="text" class="form-control" id="pf_date" value="<?= esc($issueDate) ?>" readonly>
                            <?php else: ?>
                                <div class="input-group bms-date-wrap">
                                    <input type="text" class="form-control" id="pf_date" value="<?= esc($issueDate) ?>" placeholder="DD/MM/YYYY" autocomplete="off" required>
                                    <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick date">
                                        <span aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.25"/>
                                                <path d="M5 2V5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
                                                <path d="M11 2V5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
                                                <path d="M2.5 6H13.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
                                                <path d="M5.25 8.5H5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M7.75 8.5H8.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M10.25 8.5H10.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M5.25 11H5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M7.75 11H8.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Date Of Issue is required.</div>
                            <?php endif; ?>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">Door Number</label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <input type="text" class="form-control" id="pf_addr2" value="<?= esc($initialAddr2) ?>" <?= $isReadonly ? 'readonly' : '' ?>>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">City</label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <input type="text" class="form-control" id="pf_city" value="<?= esc($initialCity) ?>" <?= $isReadonly ? 'readonly' : '' ?>>
                        </div>

                        <div class="<?= $fieldLabelColClass ?>">
                            <label class="form-label mb-0 text-md-end fw-semibold">PinCode</label>
                        </div>
                        <div class="<?= $fieldInputColClass ?>">
                            <input type="text" class="form-control" id="pf_pincode" value="<?= esc($initialPincode) ?>" <?= $isReadonly ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 bms-items-box">
                <div class="fw-semibold mb-2">Items<?= $isReadonly ? '' : ' <span class="text-danger">*</span>' ?></div>
                <div class="table-responsive">
                    <table id="pfItemsTable" class="table table-bordered align-middle nowrap w-100 mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Item Description</th>
                            <th class="text-end" style="width:96px;">Quantity</th>
                            <th style="width:82px;">Unit</th>
                            <th class="text-end" style="width:118px;">Price</th>
                            <th class="text-end" style="width:118px;">Value</th>
                            <?php if (! $isReadonly): ?>
                                <th class="text-center" style="width:74px;">Actions</th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (! $isCreate): ?>
                            <?php foreach ($items as $it): ?>
                                <?php
                                    $desc = (string) ($it['description'] ?? '');
                                    $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $desc) ?: [])));
                                    $qty = number_format((float) ($it['quantity'] ?? 0), 2, '.', '');
                                    $price = number_format((float) ($it['unit_price'] ?? 0), 2, '.', '');
                                    $amount = number_format((float) ($it['amount'] ?? 0), 2, '.', '');
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($isReadonly): ?>
                                            <div class="form-control form-control-sm bg-body" style="min-height:90px;">
                                                <ul class="mb-0 ps-3">
                                                    <?php if ($lines !== []): ?>
                                                        <?php foreach ($lines as $line): ?>
                                                            <li><?= esc($line) ?></li>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <li>-</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        <?php else: ?>
                                            <input type="hidden" class="pf-item-id" value="<?= esc((string) ($it['id'] ?? '0')) ?>">
                                            <div class="form-control form-control-sm pf-item-desc-editor" contenteditable="true" data-placeholder="Description (one bullet per line)" style="min-height:90px;">
                                                <ul>
                                                    <?php if ($lines !== []): ?>
                                                        <?php foreach ($lines as $line): ?>
                                                            <li><?= esc($line) ?></li>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <li><br></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><input type="<?= $isReadonly ? 'text' : 'number' ?>" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-qty" value="<?= esc($qty) ?>" <?= $isReadonly ? 'readonly' : '' ?>></td>
                                    <td><input type="text" class="form-control form-control-sm pf-item-uom" value="Nos" <?= $isReadonly ? 'readonly' : '' ?>></td>
                                    <td><input type="<?= $isReadonly ? 'text' : 'number' ?>" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-price" value="<?= esc($price) ?>" <?= $isReadonly ? 'readonly' : '' ?>></td>
                                    <td><input type="<?= $isReadonly ? 'text' : 'number' ?>" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-amt" value="<?= esc($amount) ?>" <?= $isReadonly ? 'readonly' : '' ?>></td>
                                    <?php if (! $isReadonly): ?>
                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Row actions">
                                                <button type="button" class="btn btn-sm btn-primary bms-pf-icon-btn pf-row-add">+</button>
                                                <button type="button" class="btn btn-sm btn-danger bms-pf-icon-btn pf-row-remove">-</button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($isView && $items === []): ?>
                            <tr><td colspan="5" class="text-center text-muted">No items.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row g-3 justify-content-end mt-4">
                <div class="col-12 col-md-6 col-lg-5 col-xl-4">
                    <div class="row g-2 align-items-center">
                        <div class="<?= $summaryLabelColClass ?>">Total</div>
                        <div class="<?= $summaryValueColClass ?>">
                            <input type="text" class="form-control" id="pf_total_input" value="<?= esc(number_format($totalAmount, 2, '.', '')) ?>" readonly>
                            <span id="pf_total" class="d-none"><?= esc(number_format($totalAmount, 2, '.', '')) ?></span>
                        </div>
                    </div>

                    <div id="pf_gst_box" class="mt-2<?= $showGstFields ? '' : ' d-none' ?>">
                        <div class="row g-2 align-items-center">
                            <div class="<?= $summaryLabelColClass ?>">GST %</div>
                            <div class="<?= $summaryValueColClass ?>">
                                <input
                                    type="<?= $isReadonly ? 'text' : 'number' ?>"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    id="pf_gst_percent"
                                    value="<?= esc(number_format($gstPercent, 2, '.', '')) ?>"
                                    <?= $isReadonly ? 'readonly' : '' ?>
                                >
                            </div>
                        </div>

                        <div id="pf_cgst_row" class="row g-2 align-items-center mt-2<?= $showSplitGstRows ? '' : ' d-none' ?>">
                            <div class="<?= $summaryLabelColClass ?>">CGST</div>
                            <div class="<?= $summaryValueColClass ?>">
                                <div class="row g-2">
                                    <div class="col-6"><input type="text" class="form-control" id="pf_cgst_amt" value="<?= esc($formatPercent($cgstRate)) ?>" readonly></div>
                                    <div class="col-6"><input type="text" class="form-control" id="pf_cgst_val" value="<?= esc(number_format($cgstAmount, 2, '.', '')) ?>" readonly></div>
                                </div>
                            </div>
                        </div>
                        <div id="pf_sgst_row" class="row g-2 align-items-center mt-2<?= $showSplitGstRows ? '' : ' d-none' ?>">
                            <div class="<?= $summaryLabelColClass ?>">SGST</div>
                            <div class="<?= $summaryValueColClass ?>">
                                <div class="row g-2">
                                    <div class="col-6"><input type="text" class="form-control" id="pf_sgst_amt" value="<?= esc($formatPercent($sgstRate)) ?>" readonly></div>
                                    <div class="col-6"><input type="text" class="form-control" id="pf_sgst_val" value="<?= esc(number_format($sgstAmount, 2, '.', '')) ?>" readonly></div>
                                </div>
                            </div>
                        </div>
                        <div id="pf_igst_row" class="row g-2 align-items-center mt-2<?= $showIgstRow ? '' : ' d-none' ?>">
                            <div class="<?= $summaryLabelColClass ?>">IGST</div>
                            <div class="<?= $summaryValueColClass ?>">
                                <div class="row g-2">
                                    <div class="col-6"><input type="text" class="form-control" id="pf_igst_amt" value="<?= esc($formatPercent($igstRate)) ?>" readonly></div>
                                    <div class="col-6"><input type="text" class="form-control" id="pf_igst_val" value="<?= esc(number_format($igstAmount, 2, '.', '')) ?>" readonly></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 align-items-center mt-2">
                            <div class="<?= $summaryLabelColClass ?>">Total GST</div>
                            <div class="<?= $summaryValueColClass ?>">
                                <input type="text" class="form-control" id="pf_total_gst" value="<?= esc(number_format($totalGst, 2, '.', '')) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-2">
                        <div class="<?= $summaryLabelColClass ?>">Net Amount</div>
                        <div class="<?= $summaryValueColClass ?>">
                            <input type="text" class="form-control" id="pf_net_amount" value="<?= esc(number_format($netAmount, 2, '.', '')) ?>" readonly>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-2">
                        <div class="<?= $summaryLabelColClass ?>">Due Date<?= $isReadonly ? '' : ' <span class="text-danger">*</span>' ?></div>
                        <div class="<?= $summaryValueColClass ?>">
                            <?php if ($isReadonly): ?>
                                <input type="text" class="form-control" id="pf_due" value="<?= esc($dueDate) ?>" readonly>
                            <?php else: ?>
                                <div class="input-group bms-date-wrap">
                                    <input type="text" class="form-control" id="pf_due" value="<?= esc($dueDate) ?>" placeholder="DD/MM/YYYY" autocomplete="off" required>
                                    <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick date">
                                        <span aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.25"/>
                                                <path d="M5 2V5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
                                                <path d="M11 2V5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
                                                <path d="M2.5 6H13.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
                                                <path d="M5.25 8.5H5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M7.75 8.5H8.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M10.25 8.5H10.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M5.25 11H5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M7.75 11H8.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Due Date is required.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($isCreate): ?>
                        <div class="col-12 text-end mt-3">
                            <button class="btn btn-primary" id="btnSaveProforma" type="button" disabled>Submit</button>
                        </div>
                    <?php elseif ($isEdit): ?>
                        <div class="col-12 text-end mt-3">
                            <button class="btn btn-primary" id="btnUpdateProforma" type="button" disabled>Update Invoice</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
