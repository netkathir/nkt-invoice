<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h5 class="bms-list-title mb-0">Payment Report</h5>
                    <p class="bms-list-subtitle mb-0">Filter outstanding and paid invoices, then export a cleaner payment summary report.</p>
                </div>
                <div class="bms-list-actions">
                    <button type="button" class="btn btn-outline-success" id="prBtnDownload">Export CSV</button>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-filter-card border-0">
        <div class="card-body">
            <div class="row g-2 align-items-end justify-content-between">
                <div class="col-12 col-md-4">
                    <label class="form-label mb-1 fw-semibold">Payment Status</label>
                    <select class="form-select" id="prPaymentStatus">
                        <option value="All" selected>All</option>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Partially Paid">Partially Paid</option>
                        <option value="Fully Paid">Fully Paid</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label mb-1 fw-semibold">From Date</label>
                    <input type="text" class="form-control" id="prFilterStartDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label mb-1 fw-semibold">To Date</label>
                    <input type="text" class="form-control" id="prFilterEndDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-2 text-md-end">
                    <button type="button" class="btn btn-outline-secondary px-3" id="prBtnReset">Reset Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bms-list-panel border-0">
        <div class="bms-list-panel-head">
            <div>
                <div class="bms-list-panel-title">Listing View</div>
                <div class="bms-list-panel-text">See due amounts, balances, and payment status in one unified reporting table.</div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table id="dtPaymentReport" class="table table-striped table-bordered nowrap w-100">
                    <thead>
                    <tr>
                        <th style="width: 70px;">S.No</th>
                        <th>Invoice</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Due Date</th>
                        <th>Total Paid</th>
                        <th>Remaining Balance</th>
                        <th>Payment Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td colspan="8" class="text-center text-danger fs-5 py-4">No records found!</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initPaymentReport && window.BMS.initPaymentReport();
    });
</script>
<?= $this->endSection() ?>



