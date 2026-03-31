<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $adminName = (string) (session()->get('admin_name') ?? 'Admin');
    $defaultMonth = (string) ($defaultMonth ?? date('Y-m'));
    $monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $defaultMonthLabel = $defaultMonth;
    try {
        $defaultMonthLabel = (new DateTime($defaultMonth . '-01'))->format('F Y');
    } catch (Throwable $e) {
    }
?>
<section class="dash-shell">
    <div class="card dash-hero border-0">
        <div class="card-body p-4 p-xl-5">
            <div class="row g-4 align-items-stretch">
                <div class="col-12 col-xl-7">
                    <h2 class="dash-title">Billing Dashboard</h2>
                    <p class="dash-subtitle mb-0">
                        Review billing movement, spot outstanding receivables quickly, and jump straight into the month’s priority actions.
                    </p>

                    <div class="dash-pill-row">
                        <span class="dash-pill">Welcome back, <?= esc($adminName) ?></span>
                        <span class="dash-pill dash-pill-soft" id="dashMonthLabel"><?= esc($defaultMonthLabel) ?></span>
                    </div>

                    <div class="dash-insight" id="dashInsight">Preparing the latest billing snapshot for <?= esc($defaultMonthLabel) ?>.</div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-primary dash-btn-primary" href="<?= base_url('billable-items') ?>">Add Billable Item</a>
                        <a class="btn btn-outline-primary dash-btn-secondary" href="<?= base_url('masters/client-master') ?>">Add Client</a>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="dash-control-card h-100">
                        <div class="dash-control-head">
                            <div>
                                <div class="dash-control-value" id="dashWindowTitle"><?= esc($defaultMonthLabel) ?></div>
                            </div>
                        </div>

                        <div class="dash-control-body">
                            <label class="form-label mb-2 dash-month-label" for="dashMonth">Select Billing Month</label>
                            <input type="hidden" id="dashMonth" value="<?= esc($defaultMonth) ?>">
                            <div class="dash-month-picker" id="dashMonthPicker">
                                <button type="button" class="dash-month-trigger" id="dashMonthTrigger" aria-expanded="false" aria-controls="dashMonthPanel">
                                    <span class="dash-month-trigger-label" id="dashMonthTriggerLabel"><?= esc($defaultMonthLabel) ?></span>
                                    <span class="dash-month-trigger-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="4" width="18" height="17" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 2.5V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M16 2.5V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M3 9.5H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                </button>

                                <div class="dash-month-panel d-none" id="dashMonthPanel">
                                    <div class="dash-month-panel-head">
                                        <button type="button" class="dash-month-nav" id="dashMonthPrevYear" aria-label="Previous year">
                                            <span aria-hidden="true">&lsaquo;</span>
                                        </button>
                                        <div class="dash-month-year" id="dashMonthYear">2026</div>
                                        <button type="button" class="dash-month-nav" id="dashMonthNextYear" aria-label="Next year">
                                            <span aria-hidden="true">&rsaquo;</span>
                                        </button>
                                    </div>

                                    <div class="dash-month-grid">
                                        <?php foreach ($monthNamesShort as $idx => $monthName): ?>
                                            <button type="button" class="dash-month-chip" data-month="<?= esc(str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT)) ?>">
                                                <?= esc($monthName) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="dash-month-panel-foot">
                                        <button type="button" class="dash-month-foot-btn" id="dashMonthToday">This Month</button>
                                        <button type="button" class="dash-month-foot-btn dash-month-foot-btn-muted" id="dashMonthClose">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dash-control-note">
                            Switching the month updates both the summary table and top-line metrics instantly.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dash-metric-grid">
        <div class="card metric-card dash-metric-card dash-metric-total h-100">
            <div class="card-body">
                <div class="dash-metric-top">
                    <div>
                        <div class="dash-metric-label">Total Items</div>
                        <div class="dash-metric-tag">Volume</div>
                    </div>
                    <div class="dash-metric-icon-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <rect x="14" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <rect x="4" y="14" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <rect x="14" y="14" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-metric-main">
                    <div class="dash-metric-value" id="mTotalItems">0</div>
                </div>
                <div class="dash-metric-note">All records available for the selected month.</div>
            </div>
        </div>

        <div class="card metric-card dash-metric-card dash-metric-pending h-100">
            <div class="card-body">
                <div class="dash-metric-top">
                    <div>
                        <div class="dash-metric-label">Pending Billing</div>
                        <div class="dash-metric-tag">Attention</div>
                    </div>
                    <div class="dash-metric-icon-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M12 7v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M12 16.5h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            <path d="M10.3 3.8 3.9 15a2 2 0 0 0 1.73 3h12.74a2 2 0 0 0 1.73-3L13.7 3.8a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-metric-main">
                    <div class="dash-metric-value" id="mPendingItems">0</div>
                </div>
                <div class="dash-metric-note">Items still waiting to move into invoices.</div>
            </div>
        </div>
        <div class="card metric-card dash-metric-card dash-metric-billed h-100">
            <div class="card-body">
                <div class="dash-metric-top">
                    <div>
                        <div class="dash-metric-label">Billed Items</div>
                        <div class="dash-metric-tag">Closed</div>
                    </div>
                    <div class="dash-metric-icon-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-metric-main">
                    <div class="dash-metric-value" id="mBilledItems">0</div>
                </div>
                <div class="dash-metric-note">Completed billing activity already pushed out.</div>
            </div>
        </div>
        <div class="card metric-card dash-metric-card dash-metric-warning h-100">
            <div class="card-body">
                <div class="dash-metric-top">
                    <div>
                        <div class="dash-metric-label">Pending Amount</div>
                        <div class="dash-metric-tag">Receivables</div>
                    </div>
                    <div class="dash-metric-icon-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3v18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M16 7.5c0-1.66-1.79-3-4-3s-4 1.34-4 3 1.79 3 4 3 4 1.34 4 3-1.79 3-4 3-4-1.34-4-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-metric-main">
                    <div class="dash-metric-value" id="mPendingAmount">0.00</div>
                </div>
                <div class="dash-metric-note">Open value still sitting in receivables.</div>
            </div>
        </div>
        <div class="card metric-card dash-metric-card dash-metric-success h-100">
            <div class="card-body">
                <div class="dash-metric-top">
                    <div>
                        <div class="dash-metric-label">Billed Amount</div>
                        <div class="dash-metric-tag">Revenue</div>
                    </div>
                    <div class="dash-metric-icon-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M4 16.5 9 11l3.2 3.2L20 6.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 6.5h5v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <div class="dash-metric-main">
                    <div class="dash-metric-value" id="mBilledAmount">0.00</div>
                </div>
                <div class="dash-metric-note">Revenue value already converted into invoices.</div>
            </div>
        </div>
    </div>

    <div class="card dash-summary-card bms-list-panel border-0">
        <div class="bms-list-panel-head dash-summary-head">
            <div>
                <div class="bms-list-panel-title dash-summary-title">Client Billing Summary</div>
                <div class="bms-list-panel-text">Review pending and billed client totals from the same structured list-view model used across the system.</div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="dtClientBillingSummary" class="table table-striped table-bordered w-100">
                <thead>
                <tr>
                    <th>Client</th>
                    <th>Pending Items</th>
                    <th>Pending Amount</th>
                    <th>Billed Items</th>
                    <th>Billed Amount</th>
                    <th>Total Amount</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initDashboard && window.BMS.initDashboard({ defaultMonth: <?= json_encode($defaultMonth) ?> });
    });
</script>
<?= $this->endSection() ?>


