<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $isAdd = (string) (service('request')->getGet('add') ?? '') === '1'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Payment</h5>
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <a class="btn btn-sm btn-primary" id="payBtnAdd" href="<?= base_url('payments?add=1') ?>">Add Payment</a>
    </div>
</div>

<div id="payListPanel" class="<?= $isAdd ? 'd-none' : '' ?>">
    <div class="card">
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
    <div class="card">
            <div class="card-body py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="h5 mb-0">Record Payment</div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('payments') ?>" id="payAddBackBtnTop">Back</a>
                </div>
            </div>

            <hr class="my-3">

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-6">
                    <div class="mb-3">
                        <div class="fw-semibold text-primary-emphasis">Payment Information</div>
                    </div>

                    <div class="mt-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select" id="payCustomer" required>
                                    <option value="">-- Select Customer --</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                <select class="form-select" id="payInvoice" required disabled>
                                    <option value="">-- Select Customer First --</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div id="payInvHistory" class="d-none border rounded-3 p-3 bg-primary-subtle">
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
                                            <button type="button" class="btn btn-sm btn-info text-white" id="payTxnBtn">Transaction History</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="payDate" value="<?= esc(date('Y-m-d')) ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" min="0" step="0.01" class="form-control" id="payAmount" placeholder="0.00" required>
                                <div class="form-text" id="payAmountMax"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="payMode">
                                    <option value="">-- Select Payment Method --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="payRemarks" rows="3" placeholder="Enter any remarks or notes (optional)"></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
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
