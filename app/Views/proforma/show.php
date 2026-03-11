<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-1">Proforma: <?= esc($proforma['proforma_number']) ?></h5>
        <div class="text-muted small">
            <?= esc($proforma['client_name']) ?> · Date: <?= esc($proforma['proforma_date']) ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-light" href="<?= base_url('proforma') ?>">Back</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="text-muted small">Client</div>
                <div class="fw-semibold"><?= esc($proforma['client_name']) ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Contact</div>
                <div><?= esc(($proforma['contact_person'] ?? '') ?: '-') ?></div>
                <div class="small text-muted"><?= esc(($proforma['email'] ?? '') ?: '') ?> <?= esc(($proforma['phone'] ?? '') ? (' · ' . $proforma['phone']) : '') ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Billing Period</div>
                <div><?= esc(($proforma['billing_from'] ?? '') ?: '-') ?> to <?= esc(($proforma['billing_to'] ?? '') ?: '-') ?></div>
                <div class="text-muted small">Status: <span class="badge bg-secondary"><?= esc($proforma['status']) ?></span></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Items</div>
        <div class="fw-semibold">Total: <?= esc(number_format((float) $proforma['total_amount'], 2)) ?></div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Amount</th>
                    <th>Billing Month</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $idx => $it): ?>
                    <tr>
                        <td><?= esc((string) ($idx + 1)) ?></td>
                        <td><?= esc($it['entry_date']) ?></td>
                        <td><?= ($it['description'] ?? '') !== '' ? $it['description'] : '-' ?></td>
                        <td class="text-end"><?= esc(number_format((float) $it['quantity'], 2)) ?></td>
                        <td class="text-end"><?= esc(number_format((float) $it['unit_price'], 2)) ?></td>
                        <td class="text-end"><?= esc(number_format((float) $it['amount'], 2)) ?></td>
                        <td><?= esc(($it['billing_month'] ?? '') ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($items === []): ?>
                    <tr><td colspan="7" class="text-center text-muted">No items.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
