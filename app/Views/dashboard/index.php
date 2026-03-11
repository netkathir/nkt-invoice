<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $adminName = (string) (session()->get('admin_name') ?? 'Admin');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="min-w-0">
        <h5 class="mb-1 text-truncate">Welcome, <?= esc($adminName) ?></h5>
        <div class="text-muted small">Track your pending billing and recent activity at a glance.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary" href="<?= base_url('billable-items') ?>">Add Billable Item</a>
        <a class="btn btn-outline-primary" href="<?= base_url('masters/client-master') ?>">Add Client</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-3">
        <div class="card card-metric metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Billable Items</div>
                        <div class="fs-4 fw-semibold"><?= esc((string) $totalBillableItems) ?></div>
                    </div>
                    <div class="metric-icon bg-primary-subtle text-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 3h10v2H7V3Zm12 4H5c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2Zm0 12H5V9h14v10Zm-9-8h4v2h-4v-2Zm0 4h8v2h-8v-2Z" fill="currentColor" opacity=".85"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card card-metric metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Pending</div>
                        <div class="fs-4 fw-semibold"><?= esc((string) $pendingBilling) ?></div>
                    </div>
                    <div class="metric-icon bg-warning-subtle text-warning">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 2a10 10 0 1 0 .001 20.001A10 10 0 0 0 12 2Zm1 11h4v-2h-3V7h-2v6Z" fill="currentColor" opacity=".85"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card card-metric metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Billed</div>
                        <div class="fs-4 fw-semibold"><?= esc((string) $billedItems) ?></div>
                    </div>
                    <div class="metric-icon bg-success-subtle text-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2Z" fill="currentColor" opacity=".85"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card card-metric metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Billing Amount</div>
                        <div class="fs-4 fw-semibold"><?= esc(number_format((float) $totalBillingAmount, 2)) ?></div>
                    </div>
                    <div class="metric-icon bg-info-subtle text-info">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 1 3 5v6c0 5 3.8 9.7 9 11 5.2-1.3 9-6 9-11V5l-9-4Zm1 17.9V19h-2v-.1c-1.7-.3-3-1.8-3-3.6h2c0 1 .8 1.8 1.8 1.8h.4c.9 0 1.6-.7 1.6-1.6 0-.8-.5-1.2-1.7-1.6l-1.1-.4c-1.6-.5-2.9-1.4-2.9-3.3 0-1.8 1.3-3.2 3-3.6V5h2v.1c1.7.3 3 1.8 3 3.6h-2c0-1-.8-1.8-1.8-1.8h-.4c-.9 0-1.6.7-1.6 1.6 0 .8.6 1.2 1.7 1.6l1.1.4c1.6.5 2.9 1.4 2.9 3.3 0 1.8-1.3 3.2-3 3.6Z" fill="currentColor" opacity=".85"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Recent Billable Items</div>
                <a class="btn btn-sm btn-primary" href="<?= base_url('billable-items') ?>">Add Billable Item</a>
            </div>
            <div class="card-body">
                <table id="dtRecentBillables" class="table table-striped table-bordered nowrap w-100">
                    <thead>
                    <tr>
                        <th>Entry No</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <div class="fw-semibold">Quick Tips</div>
            </div>
            <div class="card-body">
                <div class="text-muted small mb-2">Fast workflow</div>
                <ul class="small mb-0">
                    <li>Create a client once in <span class="fw-semibold">Masters → Client Master</span>.</li>
                    <li>Add billable items daily and keep them <span class="fw-semibold">Pending</span>.</li>
                    <li>Use the Status dropdowns in lists for quick updates.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initDashboard && window.BMS.initDashboard();
    });
</script>
<?= $this->endSection() ?>
