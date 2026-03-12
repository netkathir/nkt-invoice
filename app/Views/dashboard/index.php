<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $adminName = (string) (session()->get('admin_name') ?? 'Admin');
    $defaultMonth = (string) ($defaultMonth ?? date('Y-m'));
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
    <div class="min-w-0">
        <div class="text-muted small">Welcome, <?= esc($adminName) ?></div>
        <h4 class="mb-0">Billing Dashboard</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary" href="<?= base_url('billable-items') ?>">Add Billable Item</a>
        <a class="btn btn-outline-primary" href="<?= base_url('masters/client-master') ?>">Add Client</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Billing Month</label>
                <input type="month" class="form-control" id="dashMonth" value="<?= esc($defaultMonth) ?>">
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Items</div>
                <div class="fs-4 fw-semibold" id="mTotalItems">0</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md">
        <div class="card metric-card h-100 border-warning-subtle">
            <div class="card-body">
                <div class="text-muted small">Pending Billing</div>
                <div class="fs-4 fw-semibold text-warning" id="mPendingItems">0</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md">
        <div class="card metric-card h-100 border-success-subtle">
            <div class="card-body">
                <div class="text-muted small">Billed Items</div>
                <div class="fs-4 fw-semibold text-success" id="mBilledItems">0</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md">
        <div class="card metric-card h-100 border-warning-subtle">
            <div class="card-body">
                <div class="text-muted small">Pending Amount</div>
                <div class="fs-4 fw-semibold text-warning" id="mPendingAmount">0.00</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md">
        <div class="card metric-card h-100 border-success-subtle">
            <div class="card-body">
                <div class="text-muted small">Billed Amount</div>
                <div class="fs-4 fw-semibold text-success" id="mBilledAmount">0.00</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white">
        <div class="fw-semibold">Pending Billable Items</div>
    </div>
    <div class="card-body">
        <table id="dtPendingBillables" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>Entry No</th>
                <th>Date</th>
                <th>Client</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Amount</th>
                <th class="text-end">Action</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="fw-semibold">Recently Billed Items</div>
    </div>
    <div class="card-body">
        <table id="dtRecentBilled" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>Entry No</th>
                <th>Date</th>
                <th>Client</th>
                <th>Amount</th>
                <th>Billed Date</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initDashboard && window.BMS.initDashboard({ defaultMonth: <?= json_encode($defaultMonth) ?> });
    });
</script>
<?= $this->endSection() ?>
