<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Billable Items</h5>
    <button class="btn btn-primary" id="btnAddBillable" type="button">Add Billable Item</button>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Client</label>
                <select class="form-select" id="filterClient"></select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="Pending" selected>Pending</option>
                    <option value="Billed">Billed</option>
                    <option value="">All</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtBillableItems" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>Entry No</th>
                <th>Date</th>
                <th>Client</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initBillableItems && window.BMS.initBillableItems();
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('billable_items/form') ?>
<?= $this->endSection() ?>
