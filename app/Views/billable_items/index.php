<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Billable Items</h5>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" id="btnGenerateProforma" type="button" disabled>Generate Proforma</button>
        <button class="btn btn-primary" id="btnAddBillable" type="button">Add Billable Item</button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Client</label>
                <select class="form-select" id="filterClient">
                    <option value="">All Clients</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= esc((string) $c['id']) ?>"><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="">All</option>
                    <option value="Pending">Pending</option>
                    <option value="Billed">Billed</option>
                </select>
            </div>
            <div class="col-12 col-md-5 text-md-end">
                <div class="small text-muted">
                    Tip: Click a cell under Description/Quantity/Unit Price to edit (pending items only).
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtBillableItems" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th><input class="form-check-input" type="checkbox" id="chkAll"></th>
                <th>Entry No</th>
                <th>Date</th>
                <th>Client</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Proforma No</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="billableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Billable Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="billableForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Entry Date</label>
                            <input type="date" class="form-control" name="entry_date" id="bi_entry_date" value="<?= esc(date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Client</label>
                            <select class="form-select" name="client_id" id="bi_client_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= esc((string) $c['id']) ?>"><?= esc($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="bi_description" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="quantity" id="bi_quantity" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" name="unit_price" id="bi_unit_price" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount</label>
                            <input class="form-control" id="bi_amount_preview" value="0.00" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Billing Month (Optional)</label>
                            <input class="form-control" name="billing_month" id="bi_billing_month" placeholder="Mar 2026">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveBillable">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        window.BMS = window.BMS || {};
        BMS.initBillableItems && BMS.initBillableItems();
    });
</script>
<?= $this->endSection() ?>
