<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $isAdd = (string) (service('request')->getGet('add') ?? '') === '1'; ?>
<div class="bms-list-page">
<section class="card bms-list-hero border-0">
    <div class="card-body p-4 p-xl-4">
        <div class="bms-list-hero-row">
            <div class="bms-list-copy">
                <h5 class="bms-list-title mb-0">Payment</h5>
            </div>
            <div class="bms-list-actions">
                <a class="btn btn-primary" id="payBtnAdd" href="<?= base_url('payments?add=1') ?>">Add Payment</a>
            </div>
        </div>
    </div>
</section>

<div id="payListPanel" class="<?= $isAdd ? 'd-none' : '' ?>">
    <div class="card bms-list-filter-card border-0 mb-3">
        <div class="card-body">
            <div class="row align-items-end g-3 w-100">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="text" class="form-control form-control-sm" id="payFilterStartDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="text" class="form-control form-control-sm" id="payFilterEndDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="payBtnReset">Reset Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bms-list-panel border-0">
        <div class="card-body">
            <table id="dtPayments" class="table table-striped table-bordered nowrap w-100">
                <thead>
                <tr>
                    <th style="width: 70px;">S.No</th>
                    <th>Invoice No</th>
                    <th>Customer Name</th>
                    <th>Total Paid</th>
                    <th>Remaining Balance</th>
                    <th>Payment Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div id="payAddPanel" class="<?= $isAdd ? '' : 'd-none' ?>">
    <div class="card payment-record-card border-0">
            <div class="card-body py-4 payment-record-card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 payment-record-head">
                <div>
                    <div class="payment-record-kicker">Collections</div>
                    <div class="h5 mb-0 payment-record-title">Record Payment</div>
                    <div class="payment-record-subtitle">Capture customer collections with a cleaner, easier payment workflow.</div>
                </div>
                <div class="d-flex gap-2 payment-record-head-actions">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('payments') ?>" id="payAddBackBtnTop">Back</a>
                </div>
            </div>

            <hr class="my-3 payment-record-divider">

            <div class="row justify-content-center payment-record-shell">
                <div class="col-12 col-xl-10">
                    <div class="payment-record-section-head mb-3">
                        <div class="payment-record-section-badge">Payment Information</div>
                        <div class="payment-record-section-copy">Select the customer, pick the invoice, and record the payment details below.</div>
                    </div>

                    <div class="mt-4 payment-record-form-wrap">
                        <div class="row g-3 payment-record-grid">
                            <div class="col-12 col-md-6">
                                <label class="form-label payment-record-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select" id="payCustomer" required>
                                    <option value="">-- Select Customer --</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label payment-record-label">Invoice Number <span class="text-danger">*</span></label>
                                <select class="form-select" id="payInvoice" required disabled>
                                    <option value="">-- Select Customer First --</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div id="payInvHistory" class="d-none payment-record-history">
                                    <div class="row g-3 align-items-center small">
                                        <div class="col-6 col-lg">
                                            <div class="text-muted">Invoice Date</div>
                                            <div class="fw-semibold" id="payHistInvDate">-</div>
                                        </div>
                                        <div class="col-6 col-lg">
                                            <div class="text-muted">Invoice Total</div>
                                            <div class="fw-semibold" id="payHistTotal">0.00</div>
                                        </div>
                                        <div class="col-6 col-lg">
                                            <div class="text-muted">Total Paid</div>
                                            <div class="fw-semibold" id="payHistPaid">0.00</div>
                                        </div>
                                        <div class="col-6 col-lg">
                                            <div class="text-muted">Balance</div>
                                            <div class="fw-semibold text-danger" id="payHistBal">0.00</div>
                                        </div>
                                        <div class="col-12 col-lg-auto text-lg-end">
                                            <button type="button" class="btn btn-sm btn-info text-white payment-record-history-btn" id="payTxnBtn">Transaction History</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label payment-record-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="payDate" value="<?= esc(date('Y-m-d')) ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label payment-record-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" min="0" step="0.01" class="form-control" id="payAmount" placeholder="0.00" required>
                                <div class="form-text payment-record-help" id="payAmountMax"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label payment-record-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="payMode">
                                    <option value="">-- Select Payment Method --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label payment-record-label">Remarks</label>
                                <textarea class="form-control" id="payRemarks" rows="3" placeholder="Enter any remarks or notes (optional)"></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 payment-record-actions">
                            <a class="btn btn-outline-secondary" id="payAddBackBtn" href="<?= base_url('payments') ?>">List</a>
                            <button class="btn btn-success ms-auto" id="paySaveBtn" type="button">Record Payment</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade" id="payViewModal" tabindex="-1" aria-labelledby="payViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="payViewModalLabel">Payment Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-lg-6">
                        <div class="fw-semibold" id="payViewInvoiceNo">Invoice: -</div>
                        <div class="text-muted small" id="payViewCustomer">Customer: -</div>
                    </div>
                    <div class="col-12 col-lg-6 text-lg-end">
                        <div class="small text-muted">Invoice Total: <span class="fw-semibold text-dark" id="payViewTotal">0.00</span></div>
                        <div class="small text-muted">Total Paid: <span class="fw-semibold text-dark" id="payViewPaid">0.00</span></div>
                        <div class="small text-muted">Remaining: <span class="fw-semibold text-dark" id="payViewRemaining">0.00</span></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width:70px;">S.No</th>
                            <th style="width:130px;">Payment Date</th>
                            <th style="width:140px;">Mode</th>
                            <th style="width:160px;">Reference</th>
                            <th>Remarks</th>
                            <th class="text-end" style="width:140px;">Amount</th>
                        </tr>
                        </thead>
                        <tbody id="payViewBody">
                        <tr><td colspan="6" class="text-center text-muted">No payments.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initPayments && window.BMS.initPayments();
    });
</script>
<?= $this->endSection() ?>



