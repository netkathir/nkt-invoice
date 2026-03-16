<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Daily Expense Report</h5>
    <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('day-book/daily-expense-form') ?>">Back to List</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <label class="form-label">Start Date</label>
                <div class="input-group bms-date-wrap">
                    <input type="text" class="form-control" id="derStart" placeholder="DD/MM/YYYY" autocomplete="off">
                    <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick start date">
                        <span aria-hidden="true">📅</span>
                    </button>
                    <input type="date" class="bms-native-date" id="derStartNative" tabindex="-1" aria-hidden="true">
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">End Date</label>
                <div class="input-group bms-date-wrap">
                    <input type="text" class="form-control" id="derEnd" placeholder="DD/MM/YYYY" autocomplete="off">
                    <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick end date">
                        <span aria-hidden="true">📅</span>
                    </button>
                    <input type="date" class="bms-native-date" id="derEndNative" tabindex="-1" aria-hidden="true">
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">Expense Category</label>
                <select class="form-select" id="derCategory">
                    <option value="All" selected>All Categories</option>
                </select>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-primary" id="derBtnGenerate">Generate Report</button>
            <button type="button" class="btn btn-danger" id="derBtnPdf">Export PDF</button>
            <button type="button" class="btn btn-success" id="derBtnExcel">Export Excel</button>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-4">
        <div class="card metric-card">
            <div class="card-body">
                <div class="text-muted small">Total Entries</div>
                <div class="fs-3 fw-bold" id="derTotalEntries">0</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card metric-card">
            <div class="card-body">
                <div class="text-muted small">Total Amount</div>
                <div class="fs-3 fw-bold" id="derTotalAmount">0.00</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card metric-card">
            <div class="card-body">
                <div class="text-muted small">Categories</div>
                <div class="fs-3 fw-bold" id="derCategories">0</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="fw-semibold mb-2">Summary by Category</div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="derCatTable">
                <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th class="text-end">Count</th>
                    <th class="text-end">Total Amount</th>
                </tr>
                </thead>
                <tbody>
                <tr><td colspan="3" class="text-center text-muted">No records.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="fw-semibold mb-2">Detailed Report</div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0" id="derDetailTable">
                <thead class="table-light">
                <tr>
                    <th>Expense ID</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th>Payment Method</th>
                    <th>Paid To</th>
                </tr>
                </thead>
                <tbody>
                <tr><td colspan="7" class="text-center text-muted">No records.</td></tr>
                </tbody>
                <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th class="text-end" id="derDetailTotal">0.00</th>
                    <th colspan="2"></th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initDailyExpenseReport && window.BMS.initDailyExpenseReport();
    });
</script>
<?= $this->endSection() ?>
