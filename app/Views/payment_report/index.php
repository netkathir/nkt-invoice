<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Payment Report</h5>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-2 align-items-end justify-content-between mb-3">
            <div class="col-12 col-lg-6">
                <label class="form-label mb-1 fw-semibold">Payment Status <span class="text-danger">*</span></label>
                <select class="form-select" id="prPaymentStatus">
                    <option value="All" selected>All</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Partially Paid">Partially Paid</option>
                    <option value="Fully Paid">Fully Paid</option>
                </select>
            </div>
            <div class="col-12 col-lg-6 d-flex gap-2 justify-content-lg-end">
                <button type="button" class="btn btn-success" id="prBtnDownload">Export CSV</button>
            </div>
        </div>

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
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initPaymentReport && window.BMS.initPaymentReport();
    });
</script>
<?= $this->endSection() ?>

