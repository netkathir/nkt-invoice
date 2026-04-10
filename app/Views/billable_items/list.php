<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h5 class="bms-list-title mb-0">Billable Items</h5>
                    <p class="bms-list-subtitle mb-0">Track monthly billable work, filter by client and status, and move entries into invoices from one workspace.</p>
                </div>
                <div class="bms-list-actions">
                    <button class="btn btn-outline-success" id="btnExportBillable" type="button">Export CSV</button>
                    <button class="btn btn-primary" id="btnAddBillable" type="button">Add Billable Item</button>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-filter-card border-0">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">Client</label>
                    <select class="form-select form-select-sm" id="filterClient"></select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="Pending" selected>Pending</option>
                        <option value="Billed">Billed</option>
                        <option value="">All Status</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">Billing Month</label>
                    <input type="text" class="form-control form-control-sm bms-month-picker" id="filterMonth" placeholder="Select Month">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="text" class="form-control form-control-sm bms-date-picker" id="filterStartDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="text" class="form-control form-control-sm bms-date-picker" id="filterEndDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-2 text-md-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btnResetFilters">Reset Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bms-list-panel border-0">
        <div class="bms-list-panel-head">
            <div>
                <div class="bms-list-panel-title">Listing View</div>
                <div class="bms-list-panel-text">Search, review, and convert billable entries using the same unified list style.</div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="dtBillableItems" class="table table-striped table-bordered w-100 bms-billable-table">
                <thead>
                <tr>
                    <th>Entry No</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Description</th>
                    <th>Billing Month</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
                <tr class="bms-table-filter-row">
                    <th><input type="text" class="form-control form-control-sm column-search" data-index="0" placeholder="Search No"></th>
                    <th><input type="text" class="form-control form-control-sm column-search" data-index="1" placeholder="Search Date"></th>
                    <th><input type="text" class="form-control form-control-sm column-search" data-index="2" placeholder="Search Client"></th>
                    <th><input type="text" class="form-control form-control-sm column-search" data-index="3" placeholder="Search Desc"></th>
                    <th></th>
                    <th></th>
                    <th><input type="text" class="form-control form-control-sm column-search" data-index="6" placeholder="Search Amount"></th>
                    <th></th>
                </tr>
                </thead>
            </table>
        </div>
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
<?= $this->include('billable_items/view_modal') ?>
<?= $this->endSection() ?>

