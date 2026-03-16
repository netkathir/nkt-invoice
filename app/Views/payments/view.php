<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $invoice = $invoice ?? [];
    $payments = $payments ?? [];
    $lastPayment = $payments[0] ?? null; // ordered DESC in controller

    $fmtDate = static function (?string $iso): string {
        $raw = trim((string) ($iso ?? ''));
        if ($raw === '') return '-';
        $ts = strtotime($raw);
        return $ts ? date('d M Y', $ts) : $raw;
    };

    $fmtDateTime = static function (?string $dt): string {
        $raw = trim((string) ($dt ?? ''));
        if ($raw === '') return '-';
        $ts = strtotime($raw);
        return $ts ? date('d M Y, h:i A', $ts) : $raw;
    };

    $money = static function ($v): string {
        $n = (float) str_replace(',', '', (string) ($v ?? '0'));
        return '₹' . number_format($n, 2, '.', '');
    };

    $customer = (string) ($invoice['customer_name'] ?? '-');
    $invoiceNo = (string) ($invoice['invoice_no'] ?? '-');
    $invoiceDate = (string) ($invoice['invoice_date'] ?? '');

    $paymentDate = $lastPayment ? (string) ($lastPayment['payment_date'] ?? '') : '';
    $paymentAmount = $lastPayment ? (string) ($lastPayment['amount'] ?? '0') : '0';
    $paymentMethod = $lastPayment ? (string) (($lastPayment['payment_mode'] ?? '') ?: '-') : '-';
    $createdAt = $lastPayment ? (string) (($lastPayment['created_at'] ?? '') ?: '') : '';
    $updatedAt = $lastPayment ? (string) (($lastPayment['updated_at'] ?? '') ?: '') : '';
    $createdBy = (string) (session()->get('admin_name') ?: '-');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Payment Details</h5>
    <div class="d-flex gap-2">
        <?php $invId = (int) ($invoice['id'] ?? 0); ?>
        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('payments?add=1&invoice_id=' . $invId) ?>">Edit</a>
        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('payments') ?>">Back</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10 col-xxl-8">
        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="fw-semibold text-primary-emphasis mb-3">Payment Information</div>

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="text-muted">Customer</div>
                        <div class="fw-semibold"><?= esc($customer) ?></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="text-muted">Invoice Number</div>
                        <div class="fw-semibold"><?= esc($invoiceNo) ?></div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="text-muted">Payment Date</div>
                        <div class="fw-semibold"><?= esc($fmtDate($paymentDate)) ?></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="text-muted">Payment Amount</div>
                        <div class="fw-semibold"><?= esc($money($paymentAmount)) ?></div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="text-muted">Payment Method</div>
                        <div class="fw-semibold"><?= esc($paymentMethod) ?></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="text-muted">Created By</div>
                        <div class="fw-semibold"><?= esc($createdBy) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 border-0">
            <div class="card-body p-0">
                <div class="p-4 rounded-3 bg-primary-subtle border-start border-4 border-primary">
                    <div class="fw-semibold text-primary-emphasis mb-3">Invoice Information</div>
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <div class="text-muted">Invoice Total</div>
                            <div class="fw-semibold"><?= esc($money((string) ($invoice['invoice_total'] ?? '0'))) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted">Total Paid</div>
                            <div class="fw-semibold"><?= esc($money((string) ($invoice['total_paid'] ?? '0'))) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted">Balance</div>
                            <div class="fw-semibold text-danger"><?= esc($money((string) ($invoice['remaining'] ?? '0'))) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted">Invoice Date</div>
                            <div class="fw-semibold"><?= esc($fmtDate($invoiceDate)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <div class="fw-semibold text-primary-emphasis mb-3">Additional Information</div>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="text-muted">Created At</div>
                        <div class="fw-semibold"><?= esc($fmtDateTime($createdAt)) ?></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="text-muted">Last Updated</div>
                        <div class="fw-semibold"><?= esc($fmtDateTime($updatedAt)) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a class="btn btn-outline-secondary" href="<?= base_url('payments') ?>">Back to List</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
