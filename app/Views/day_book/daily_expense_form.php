<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <span class="bms-list-kicker">Expense Ledger</span>
                    <h5 class="bms-list-title mb-0">Daily Expense</h5>
                    <p class="bms-list-subtitle mb-0">Track expense entries, search vendors and categories, and manage day-book records from one list workspace.</p>
                </div>
                <div class="bms-list-actions">
                    <a class="btn btn-primary" href="<?= base_url('day-book/daily-expense-form/create') ?>">New Daily Expense Entry</a>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-filter-card border-0">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-10">
                    <input type="text" class="form-control" id="deSearch" placeholder="Search by expense ID, paid to, category, or description...">
                </div>
                <div class="col-12 col-lg-2 d-grid">
                    <button type="button" class="btn btn-info text-white" id="deBtnSearch">Search</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bms-list-panel border-0">
        <div class="bms-list-panel-head">
            <div>
                <div class="bms-list-panel-kicker">Expense Register</div>
                <div class="bms-list-panel-title">Listing View</div>
                <div class="bms-list-panel-text">Browse daily expenses, amounts, and payment methods in the shared register layout.</div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="dtDailyExpenses" class="table table-striped table-bordered nowrap w-100">
                <thead>
                <tr>
                    <th style="width: 70px;">S.No</th>
                    <th>Expense ID</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th>Payment Method</th>
                    <th>Paid To</th>
                    <th style="width: 170px;">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade" id="deModal" tabindex="-1" aria-labelledby="deModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="deModalLabel">Daily Expense Entry</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deId" value="">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="deDate" value="<?= esc(date('Y-m-d')) ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" id="deCategory" placeholder="Transportation">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="deMethod">
                            <option value="">Select</option>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Paid To</label>
                        <input type="text" class="form-control" id="dePaidTo" placeholder="Supplier">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" min="0" step="0.01" class="form-control" id="deAmount" placeholder="0.00">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="deDesc" rows="3" placeholder="Description..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="button" id="deBtnSave">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deViewModal" tabindex="-1" aria-labelledby="deViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="deViewModalLabel">View Expense</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="text-muted small">Expense ID</div>
                        <div class="fw-semibold" id="deVCode">-</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="text-muted small">Date</div>
                        <div class="fw-semibold" id="deVDate">-</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">Category</div>
                        <div class="fw-semibold" id="deVCat">-</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">Amount</div>
                        <div class="fw-semibold" id="deVAmt">0.00</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">Payment Method</div>
                        <div class="fw-semibold" id="deVMethod">-</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="text-muted small">Paid To</div>
                        <div class="fw-semibold" id="deVPaidTo">-</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Description</div>
                        <div class="fw-semibold" id="deVDesc">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initDailyExpenseForm && window.BMS.initDailyExpenseForm();
    });
</script>
<?= $this->endSection() ?>
