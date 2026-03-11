<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Create Proforma Invoice</h5>
    <a class="btn btn-light" href="<?= base_url('proforma') ?>">Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label">Client <span class="text-danger">*</span></label>
                <select class="form-select" id="pf_client_id" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $c): ?>
                        <?php
                            $label = $c['name'] ?: ($c['contact_person'] ?: ($c['email'] ?: ($c['phone'] ?: ('Client #' . $c['id']))));
                        ?>
                        <option value="<?= esc((string) $c['id']) ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Proforma Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="pf_date" value="<?= esc(date('Y-m-d')) ?>" required>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Billing From</label>
                <input type="date" class="form-control" id="pf_from">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Billing To</label>
                <input type="date" class="form-control" id="pf_to">
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Pending Billable Items</div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-muted small">Total Amount: <span class="fw-semibold">₹ <span id="pf_total">0.00</span></span></div>
            <button class="btn btn-primary" id="btnSaveProforma" type="button" disabled>Save Proforma</button>
        </div>
    </div>
    <div class="card-body">
        <table id="dtProformaItems" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th><input class="form-check-input" type="checkbox" id="pf_chkAll"></th>
                <th>Entry No</th>
                <th>Date</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Amount</th>
                <th>Billing Month</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initProformaCreate && window.BMS.initProformaCreate();
    });
</script>
<?= $this->endSection() ?>
