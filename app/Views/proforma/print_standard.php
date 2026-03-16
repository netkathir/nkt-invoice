<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
    $currency = (string) (($proforma['currency'] ?? '') ?: 'INR');
    $currencySymbol = match ($currency) {
        'INR' => '₹',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        default => $currency . ' ',
    };

    $issueDate = (string) (($proforma['proforma_date'] ?? '') ?: '');
    $dueDate = (string) (($proforma['billing_to'] ?? '') ?: '');

    $billToName = (string) (($proforma['client_name'] ?? '') ?: '');
    $billToContact = (string) (($proforma['contact_person'] ?? '') ?: '');
    $billToEmail = (string) (($proforma['email'] ?? '') ?: '');
    $billToPhone = (string) (($proforma['phone'] ?? '') ?: '');
    $billToAddr1 = trim((string) (($proforma['billing_address'] ?? '') ?: ($proforma['address'] ?? '')));
    $billToCity = (string) (($proforma['city'] ?? '') ?: '');
    $billToState = (string) (($proforma['state'] ?? '') ?: '');
    $billToCountry = (string) (($proforma['country'] ?? '') ?: '');
    $billToPostal = (string) (($proforma['postal_code'] ?? '') ?: '');

    $fromName = (string) (config('Email')->fromName ?? 'Billing Management System');
    $fromEmail = (string) (config('Email')->fromEmail ?? '');

    $invoiceType = (string) (($proforma['invoice_type'] ?? '') ?: 'Invoice');
    $subTotal = (float) ($proforma['total_amount'] ?? 0);
    $netAmount = (float) (($proforma['net_amount'] ?? null) ?? $subTotal);
    $totalGst = (float) (($proforma['total_gst'] ?? null) ?? 0);
    $cgst = (float) (($proforma['cgst_amount'] ?? null) ?? 0);
    $sgst = (float) (($proforma['sgst_amount'] ?? null) ?? 0);
    $igst = (float) (($proforma['igst_amount'] ?? null) ?? 0);
    $gstPercent = (float) (($proforma['gst_percent'] ?? null) ?? 0);
    $gstMode = (string) (($proforma['gst_mode'] ?? '') ?: '');
?>

<style>
    .inv-page{
        background: #fff;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        padding: 22px;
    }
    .inv-meta-label{
        color: rgba(0,0,0,.55);
        font-size: .82rem;
    }
    .inv-title{
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: .08em;
    }
    .inv-logo{
        width: 120px;
        height: auto;
        object-fit: contain;
    }
    .inv-items th, .inv-items td{
        vertical-align: top;
    }
    .inv-items thead th{
        background: rgba(0,0,0,.03);
    }
    .inv-small{
        font-size: .9rem;
    }
    .inv-muted{
        color: rgba(0,0,0,.55);
    }
    .inv-sign{
        border-top: 1px solid rgba(0,0,0,.2);
        width: 220px;
        margin-left: auto;
        margin-top: 42px;
        padding-top: 8px;
        text-align: center;
        font-size: .9rem;
    }
    @media print{
        body{ background: #fff !important; }
        .inv-toolbar{ display: none !important; }
        .inv-page{
            border: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
        }
        .container{ max-width: 100% !important; }
        @page{ size: A4; margin: 12mm; }
        a[href]:after{ content: none !important; }
    }
</style>

<div class="inv-toolbar d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <a class="btn btn-sm btn-light" href="<?= base_url('proforma') ?>">Back</a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
    </div>
    <div class="text-muted small">
        Tip: Use browser “Save as PDF” if needed.
    </div>
</div>

<div class="inv-page">
    <div class="d-flex justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-start gap-3">
            <img src="<?= base_url('assets/img/Netkathir_logo.png') ?>" alt="Logo" class="inv-logo">
            <div>
                <div class="fw-semibold"><?= esc($fromName) ?></div>
                <?php if ($fromEmail !== ''): ?>
                    <div class="inv-muted inv-small"><?= esc($fromEmail) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end">
            <div class="inv-title"><?= esc(strtoupper($invoiceType)) ?></div>
            <div class="inv-muted inv-small">Invoice No: <span class="fw-semibold text-dark"><?= esc((string) ($proforma['proforma_number'] ?? '')) ?></span></div>
            <?php if ($issueDate !== ''): ?>
                <div class="inv-muted inv-small">Date: <span class="fw-semibold text-dark"><?= esc($issueDate) ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-3">

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="inv-meta-label mb-1">Bill To</div>
            <div class="fw-semibold"><?= esc($billToName) ?></div>
            <?php if ($billToContact !== ''): ?>
                <div class="inv-small"><?= esc($billToContact) ?></div>
            <?php endif; ?>
            <?php if ($billToAddr1 !== ''): ?>
                <div class="inv-small"><?= nl2br(esc($billToAddr1)) ?></div>
            <?php endif; ?>
            <?php
                $place = trim(implode(', ', array_values(array_filter([$billToCity, $billToState, $billToCountry]))));
                $pin = trim($billToPostal);
                $placeLine = trim($place . ($pin !== '' ? (' - ' . $pin) : ''));
            ?>
            <?php if ($placeLine !== ''): ?>
                <div class="inv-small"><?= esc($placeLine) ?></div>
            <?php endif; ?>
            <?php if ($billToEmail !== '' || $billToPhone !== ''): ?>
                <div class="inv-muted inv-small">
                    <?= esc($billToEmail) ?>
                    <?= $billToEmail !== '' && $billToPhone !== '' ? ' · ' : '' ?>
                    <?= esc($billToPhone) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-md-6">
            <div class="row g-2">
                <div class="col-6">
                    <div class="inv-meta-label">Billing From</div>
                    <div class="inv-small fw-semibold"><?= esc((string) (($proforma['billing_from'] ?? '') ?: '-')) ?></div>
                </div>
                <div class="col-6">
                    <div class="inv-meta-label">Billing To</div>
                    <div class="inv-small fw-semibold"><?= esc((string) (($proforma['billing_to'] ?? '') ?: '-')) ?></div>
                </div>
                <div class="col-6">
                    <div class="inv-meta-label">Currency</div>
                    <div class="inv-small fw-semibold"><?= esc($currency) ?></div>
                </div>
                <?php if ($dueDate !== ''): ?>
                    <div class="col-12">
                        <div class="inv-meta-label">Due Date</div>
                        <div class="inv-small fw-semibold"><?= esc($dueDate) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-bordered inv-items mb-0">
            <thead>
            <tr>
                <th style="width: 54px;">S.No</th>
                <th>Description</th>
                <th class="text-end" style="width: 90px;">Qty</th>
                <th class="text-end" style="width: 130px;">Unit Price</th>
                <th class="text-end" style="width: 140px;">Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (($items ?? []) as $idx => $it): ?>
                <?php
                    $desc = (string) ($it['description'] ?? '');
                    $lines = array_values(array_filter(array_map('trim', preg_split('/\\r?\\n/', $desc) ?: [])));
                    $itemName = $lines[0] ?? '';
                    $rest = $lines;
                    if ($rest !== []) {
                        array_shift($rest);
                    }
                ?>
                <tr>
                    <td><?= esc((string) ($idx + 1)) ?></td>
                    <td>
                        <?php if ($itemName !== ''): ?>
                            <div class="fw-semibold"><?= esc($itemName) ?></div>
                        <?php endif; ?>
                        <?php if ($rest !== []): ?>
                            <ul class="mb-0 ps-3 inv-small">
                                <?php foreach ($rest as $line): ?>
                                    <li><?= esc($line) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if ($itemName === '' && $rest === []): ?>
                            <span class="inv-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?= esc(number_format((float) ($it['quantity'] ?? 0), 2)) ?></td>
                    <td class="text-end"><?= esc($currencySymbol . number_format((float) ($it['unit_price'] ?? 0), 2)) ?></td>
                    <td class="text-end"><?= esc($currencySymbol . number_format((float) ($it['amount'] ?? 0), 2)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (($items ?? []) === []): ?>
                <tr><td colspan="5" class="text-center inv-muted">No items.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-12 col-md-6">
            <div class="inv-meta-label">Notes</div>
            <div class="inv-small inv-muted">This is a computer generated invoice.</div>
        </div>
        <div class="col-12 col-md-6">
            <table class="table table-sm mb-0">
                <tbody>
                <tr>
                    <td class="text-end inv-meta-label">Sub Total</td>
                    <td class="text-end fw-semibold" style="width: 180px;"><?= esc($currencySymbol . number_format($subTotal, 2)) ?></td>
                </tr>
                <?php if ($gstPercent > 0 || $totalGst > 0): ?>
                    <tr>
                        <td class="text-end inv-meta-label">GST (<?= esc(number_format($gstPercent, 2)) ?>%) <?= $gstMode !== '' ? (' · ' . esc($gstMode)) : '' ?></td>
                        <td class="text-end fw-semibold"><?= esc($currencySymbol . number_format($totalGst, 2)) ?></td>
                    </tr>
                    <?php if ($cgst > 0): ?>
                        <tr>
                            <td class="text-end inv-meta-label">CGST</td>
                            <td class="text-end"><?= esc($currencySymbol . number_format($cgst, 2)) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($sgst > 0): ?>
                        <tr>
                            <td class="text-end inv-meta-label">SGST</td>
                            <td class="text-end"><?= esc($currencySymbol . number_format($sgst, 2)) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($igst > 0): ?>
                        <tr>
                            <td class="text-end inv-meta-label">IGST</td>
                            <td class="text-end"><?= esc($currencySymbol . number_format($igst, 2)) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
                <tr>
                    <td class="text-end inv-meta-label">Net Amount</td>
                    <td class="text-end fw-bold"><?= esc($currencySymbol . number_format($netAmount, 2)) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="inv-sign">Authorized Signature</div>
</div>

<?php if (! empty($autoprint)): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () { window.print(); }, 250);
        });
    </script>
<?php endif; ?>
<?= $this->endSection() ?>
