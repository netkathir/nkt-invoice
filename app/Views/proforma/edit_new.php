<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $editIssueDate = !empty($proforma['proforma_date']) ? date('d/m/Y', strtotime((string) $proforma['proforma_date'])) : '';
    $editDueDate = !empty($proforma['billing_to']) ? date('d/m/Y', strtotime((string) $proforma['billing_to'])) : '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Edit Invoice</h5>
    <a class="btn btn-light" href="<?= base_url('proforma') ?>">Back</a>
</div>

<div class="card bms-invoice-card">
    <div class="card-body">
        <input type="hidden" id="pf_id" value="<?= esc((string) ($proforma['id'] ?? '')) ?>">

        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="flex-shrink-0">
                <img src="<?= base_url('favicon_netk.png') ?>" alt="Company Logo" class="bms-invoice-logo">
            </div>
            <div class="flex-grow-1">
                <div class="h5 mb-1">Invoice</div>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Invoice No <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_invoice_no" value="<?= esc((string) ($proforma['proforma_number'] ?? '')) ?>" placeholder="Enter invoice no" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Customer Name <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <select class="form-select" id="pf_client_id" required disabled>
                            <option value="">Select</option>
                            <?php foreach (($clients ?? []) as $c): ?>
                                <?php
                                    $customer = $c['contact_person'] ?: ($c['name'] ?: ($c['email'] ?: ($c['phone'] ?: ('Client #' . $c['id']))));
                                    $company = $c['name'] ?: $customer;
                                    $gst = $c['gst_no'] ?? ($c['gst'] ?? '');
                                    $addr1 = $c['address'] ?? '';
                                    $addr2 = $c['billing_address'] ?? '';
                                    $city = $c['city'] ?? '';
                                    $state = $c['state'] ?? '';
                                    $pincode = $c['postal_code'] ?? ($c['pincode'] ?? '');
                                    $selected = (int) ($c['id'] ?? 0) === (int) ($proforma['client_id'] ?? 0);
                                ?>
                                <option
                                    value="<?= esc((string) $c['id']) ?>"
                                    data-company="<?= esc((string) $company) ?>"
                                    data-customer="<?= esc((string) $customer) ?>"
                                    data-gst="<?= esc((string) $gst) ?>"
                                    data-addr1="<?= esc((string) $addr1) ?>"
                                    data-addr2="<?= esc((string) $addr2) ?>"
                                    data-city="<?= esc((string) $city) ?>"
                                    data-state="<?= esc((string) $state) ?>"
                                    data-country="<?= esc((string) ($c['country'] ?? '')) ?>"
                                    data-pincode="<?= esc((string) $pincode) ?>"
                                    <?= $selected ? 'selected' : '' ?>
                                ><?= esc($customer) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="pf_from" value="<?= esc((string) ($proforma['billing_from'] ?? '')) ?>">
                    </div>

                    <div class="col-12 col-md-4" id="pf_gst_row">
                        <label class="form-label mb-0 text-md-end fw-semibold">GST NO</label>
                    </div>
                    <div class="col-12 col-md-8" id="pf_gst_col">
                        <input type="text" class="form-control" id="pf_gst" value="" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Address Line2</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_addr2" value="" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">State</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_state" value="" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Currency <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <?php $currency = (string) (($proforma['currency'] ?? '') ?: 'INR'); ?>
                        <select class="form-select" id="pf_currency" required>
                            <option value="">Select</option>
                            <option value="INR" <?= $currency === 'INR' ? 'selected' : '' ?>>INR</option>
                            <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD</option>
                            <option value="EUR" <?= $currency === 'EUR' ? 'selected' : '' ?>>EUR</option>
                            <option value="GBP" <?= $currency === 'GBP' ? 'selected' : '' ?>>GBP</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <?php $invoiceType = (string) (($proforma['invoice_type'] ?? '') ?: 'GST Invoice'); ?>
                        <select class="form-select" id="pf_invoice_type" required disabled>
                            <option value="GST Invoice" <?= $invoiceType === 'GST Invoice' ? 'selected' : '' ?>>GST Invoice</option>
                            <option value="Export Invoice" <?= $invoiceType === 'Export Invoice' ? 'selected' : '' ?>>Export Invoice</option>
                        </select>
                        <div class="form-text">Auto-selected from client country.</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Company Name</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_company" value="" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Date Of Issue <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="input-group bms-date-wrap">
                            <input type="text" class="form-control" id="pf_date" value="<?= esc($editIssueDate) ?>" placeholder="DD/MM/YYYY" autocomplete="off" required>
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
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Address Line1</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_addr1" value="" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">City</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_city" value="" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">PinCode</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" id="pf_pincode" value="" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 bms-items-box">
            <div class="fw-semibold mb-2">Items <span class="text-danger">*</span></div>
            <div class="table-responsive">
                <table id="pfItemsTable" class="table table-bordered align-middle nowrap w-100 mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Item Description</th>
                        <th class="text-end" style="width:120px;">Quantity</th>
                        <th style="width:110px;">UOM</th>
                        <th class="text-end" style="width:140px;">Price</th>
                        <th class="text-end" style="width:140px;">Value</th>
                        <th class="text-center" style="width:90px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (($items ?? []) as $it): ?>
                        <?php
                            $desc = (string) ($it['description'] ?? '');
                            $lines = array_values(array_filter(array_map('trim', preg_split('/\\r?\\n/', $desc) ?: [])));
                        ?>
                        <tr>
                            <td>
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
                            </td>
                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-qty" value="<?= esc(number_format((float) ($it['quantity'] ?? 1), 2, '.', '')) ?>"></td>
                            <td><input type="text" class="form-control form-control-sm pf-item-uom" value="Nos"></td>
                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-price" value="<?= esc(number_format((float) ($it['unit_price'] ?? 0), 2, '.', '')) ?>"></td>
                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end pf-item-amt" value="<?= esc(number_format((float) ($it['amount'] ?? 0), 2, '.', '')) ?>"></td>
                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="Row actions">
                                    <button type="button" class="btn btn-sm btn-primary bms-pf-icon-btn pf-row-add">+</button>
                                    <button type="button" class="btn btn-sm btn-danger bms-pf-icon-btn pf-row-remove">-</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-3 justify-content-end mt-4">
            <div class="col-12 col-md-6 col-lg-5 col-xl-4">
                <div class="row g-2 align-items-center">
                    <div class="col-4 text-end fw-semibold">Total</div>
                    <div class="col-8">
                        <input type="text" class="form-control" id="pf_total_input" value="0.00" readonly>
                        <span id="pf_total" class="d-none">0.00</span>
                    </div>
                </div>

                <div id="pf_gst_box" class="mt-2">
                    <?php $gstPercent = (float) ($proforma['gst_percent'] ?? 0); ?>
                    <?php $gstMode = (string) (($proforma['gst_mode'] ?? '') ?: 'CGST_SGST'); ?>
                    <div class="row g-2 align-items-center">
                        <div class="col-4 text-end fw-semibold">GST %</div>
                        <div class="col-8">
                            <input type="number" min="0" step="0.01" class="form-control" id="pf_gst_percent" value="<?= esc(number_format($gstPercent, 2, '.', '')) ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-1">
                        <div class="col-4"></div>
                        <div class="col-8 d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pf_gst_mode" id="pf_mode_cgst" value="CGST_SGST" <?= $gstMode === 'IGST' ? '' : 'checked' ?>>
                                <label class="form-check-label" for="pf_mode_cgst">CGST &amp; SGST</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pf_gst_mode" id="pf_mode_igst" value="IGST" <?= $gstMode === 'IGST' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pf_mode_igst">IGST</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">CGST</div>
                        <div class="col-8">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" class="form-control" id="pf_cgst_amt" value="0.00" readonly></div>
                                <div class="col-6"><input type="text" class="form-control" id="pf_cgst_val" value="0.00" readonly></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">SGST</div>
                        <div class="col-8">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" class="form-control" id="pf_sgst_amt" value="0.00" readonly></div>
                                <div class="col-6"><input type="text" class="form-control" id="pf_sgst_val" value="0.00" readonly></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">IGST</div>
                        <div class="col-8">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" class="form-control" id="pf_igst_amt" value="0.00" readonly></div>
                                <div class="col-6"><input type="text" class="form-control" id="pf_igst_val" value="0.00" readonly></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">Total GST</div>
                        <div class="col-8">
                            <input type="text" class="form-control" id="pf_total_gst" value="0.00" readonly>
                        </div>
                    </div>
                </div>

                <div class="row g-2 align-items-center mt-2">
                    <div class="col-4 text-end fw-semibold">Net Amount</div>
                    <div class="col-8">
                        <input type="text" class="form-control" id="pf_net_amount" value="0.00" readonly>
                    </div>
                </div>

                <div class="row g-2 align-items-center mt-2">
                    <div class="col-4 text-end fw-semibold">Due Date <span class="text-danger">*</span></div>
                    <div class="col-8">
                        <div class="input-group bms-date-wrap">
                            <input type="text" class="form-control" id="pf_due" value="<?= esc($editDueDate) ?>" placeholder="DD/MM/YYYY" autocomplete="off" required>
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
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button class="btn btn-primary" id="btnUpdateProforma" type="button" disabled>Update Invoice</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformaEdit && window.BMS.initProformaEdit();
    });
</script>
<?= $this->endSection() ?>

