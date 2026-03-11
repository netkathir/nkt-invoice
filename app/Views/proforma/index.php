<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Proforma Invoices</h5>
    <a class="btn btn-primary" href="<?= base_url('proforma/create') ?>">Create Proforma</a>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtProformas" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>Proforma Number</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total Amount</th>
                <th>Status</th>
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
