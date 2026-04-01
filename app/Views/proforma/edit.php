<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $legacyIssueDate = !empty($proforma['proforma_date']) ? date('d/m/Y', strtotime((string) $proforma['proforma_date'])) : '';
    $legacyBillingFrom = !empty($proforma['billing_from']) ? date('d/m/Y', strtotime((string) $proforma['billing_from'])) : '';
    $legacyBillingTo = !empty($proforma['billing_to']) ? date('d/m/Y', strtotime((string) $proforma['billing_to'])) : '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Edit Invoice</h5>
    <a class="btn btn-light" href="<?= base_url('proforma') ?>">Back</a>
</div>

<div class="card bms-invoice-card mb-3">
    <div class="card-body">
        <input type="hidden" id="pf_id" value="<?= esc((string) $proforma['id']) ?>">

        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="flex-shrink-0">
                <img src="<?= base_url('favicon_netk.png') ?>" alt="Company Logo" class="bms-invoice-logo">
            </div>
            <div class="flex-grow-1">
                <div class="h5 mb-1">Invoice</div>
            </div>
        </div>

        <hr class="my-3">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label">Client</label>
                <select class="form-select" id="pf_client_id" disabled>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= esc((string) $c['id']) ?>" <?= (int) $c['id'] === (int) $proforma['client_id'] ? 'selected' : '' ?>>
                            <?php
                                $label = $c['name'] ?: ($c['contact_person'] ?: ($c['email'] ?: ($c['phone'] ?: ('Client #' . $c['id']))));
                            ?>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Date Of Issue <span class="text-danger">*</span></label>
                <div class="input-group bms-date-wrap">
                    <input type="text" class="form-control" id="pf_date" value="<?= esc($legacyIssueDate) ?>" placeholder="DD/MM/YYYY" autocomplete="off" required>
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
            <div class="col-12 col-md-2">
                <label class="form-label">Billing From</label>
                <div class="input-group bms-date-wrap">
                    <input type="text" class="form-control" id="pf_from" value="<?= esc($legacyBillingFrom) ?>" placeholder="DD/MM/YYYY" autocomplete="off">
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
            <div class="col-12 col-md-2">
                <label class="form-label">Billing To</label>
                <div class="input-group bms-date-wrap">
                    <input type="text" class="form-control" id="pf_to" value="<?= esc($legacyBillingTo) ?>" placeholder="DD/MM/YYYY" autocomplete="off">
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
        <div class="row g-3 mt-0">
            <div class="col-12 col-md-4">
                <label class="form-label">Invoice No <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="pf_invoice_no" value="<?= esc((string) $proforma['proforma_number']) ?>" required>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Billable Items</div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-muted small">Total Amount: <span class="fw-semibold">Rs <span id="pf_total">0.00</span></span></div>
            <button class="btn btn-primary" id="btnUpdateProforma" type="button" disabled>Update Invoice</button>
        </div>
    </div>
    <div class="card-body">
        <table id="dtProformaItems" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th><input class="form-check-input" type="checkbox" id="pf_chkAll"></th>
                <th>Entry No</th>
                <th>Date</th>
                <th>Item Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Amount</th>
                <th>Billing Month</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<?= view('billable_items/view_modal') ?>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformaEdit && window.BMS.initProformaEdit();
    });
</script>
<?= $this->endSection() ?>
