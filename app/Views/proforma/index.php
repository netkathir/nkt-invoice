<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Invoices</h5>
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <button class="btn btn-sm btn-outline-success" id="pfBtnExport" type="button">Export CSV</button>
        <a class="btn btn-sm btn-primary" href="<?= base_url('proforma/create') ?>">Add Invoice</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtProformas" class="table table-striped table-bordered w-100">
            <thead>
            <tr>
                <th style="width: 70px;">S.No</th>
                <th>Invoice No</th>
                <th>Invoice Type</th>
                <th>Date of Issue</th>
                <th>Due Date</th>
                <th>Customer Name</th>
                <th>Company Name</th>
                <th>Net Amount</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformas && window.BMS.initProformas();
    });
</script>
<?= $this->endSection() ?>
