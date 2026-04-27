<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="invoice-list-shell">
    <section class="card invoice-list-stage border-0">
        <div class="card-body p-3 p-xl-3">
            <div class="invoice-list-stage-layout">
                <div class="invoice-list-stage-main">
                    <div class="invoice-list-stage-copy">
                        <h5 class="invoice-list-stage-title mb-2">Invoices</h5>
                    </div>
                </div>

                <div class="invoice-list-actions">
                    <button class="btn btn-outline-success" id="pfBtnExport" type="button">Export CSV</button>
                    <a class="btn btn-primary" href="<?= base_url('proforma/create') ?>">Add Invoice</a>
                </div>

                <aside class="invoice-list-stage-side">
                    <section class="invoice-list-overview" aria-label="Invoice summary">
                        <article class="invoice-list-stat invoice-list-stat-primary">
                            <div class="invoice-list-stat-label">Total Invoices</div>
                            <div class="invoice-list-stat-value" id="pfStatTotal">0</div>
                        </article>

                        <article class="invoice-list-stat invoice-list-stat-teal">
                            <div class="invoice-list-stat-label">Export Bills</div>
                            <div class="invoice-list-stat-value" id="pfStatExport">0</div>
                        </article>

                        <article class="invoice-list-stat invoice-list-stat-amber">
                            <div class="invoice-list-stat-label">GST Bills</div>
                            <div class="invoice-list-stat-value" id="pfStatGst">0</div>
                        </article>

                        <article class="invoice-list-stat invoice-list-stat-ink">
                            <div class="invoice-list-stat-label">Total Ledger Value</div>
                            <div class="invoice-list-stat-value" id="pfStatAmount">0.00</div>
                        </article>
                    </section>
                </aside>
            </div>
        </div>
    </section>

    <div class="card invoice-list-filter-card border-0 mb-1">
        <div class="card-body">
            <div class="row align-items-end g-3 w-100">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="text" class="form-control form-control-sm" id="pfFilterStartDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="text" class="form-control form-control-sm" id="pfFilterEndDate" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="pfBtnReset">Reset Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card invoice-list-table-card border-0">
        <div class="card-body">
            <div class="table-responsive invoice-list-table-wrap">
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
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformas && window.BMS.initProformas();
    });
</script>
<?= $this->endSection() ?>





