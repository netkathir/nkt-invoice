<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
    $currency = (string) (($proforma['currency'] ?? '') ?: 'INR');
    $formatMoney = static function (float $amount) use ($currency): string {
        $prefix = match ($currency) {
            'INR' => '&#8377; ',
            'USD' => '$ ',
            'EUR' => '&euro; ',
            'GBP' => '&pound; ',
            default => esc($currency) . ' ',
        };

        return $prefix . number_format($amount, 2);
    };

    $issueDate = trim((string) ($proforma['proforma_date'] ?? ''));
    $invoiceNo = trim((string) ($proforma['proforma_number'] ?? ''));
    $invoiceType = (string) (($proforma['invoice_type'] ?? '') ?: 'GST Invoice');
    $isExportInvoice = $invoiceType === 'Export Invoice';

    $billToName = trim((string) (($proforma['contact_person'] ?? '') ?: ($proforma['client_name'] ?? '')));
    $billToCompany = trim((string) ($proforma['client_name'] ?? ''));
    $billToEmail = trim((string) ($proforma['email'] ?? ''));
    $billToPhone = trim((string) ($proforma['phone'] ?? ''));
    $billToGst = trim((string) ($proforma['gst_no'] ?? ''));
    $billToAddress = trim((string) (($proforma['billing_address'] ?? '') ?: ($proforma['address'] ?? '')));
    $billToCity = trim((string) ($proforma['city'] ?? ''));
    $billToState = trim((string) ($proforma['state'] ?? ''));
    $billToCountry = trim((string) ($proforma['country'] ?? ''));
    $billToPostal = trim((string) ($proforma['postal_code'] ?? ''));
    $billToPlace = trim(implode(', ', array_values(array_filter([$billToCity, $billToState, $billToCountry]))));
    if ($billToPostal !== '') {
        $billToPlace = trim($billToPlace . ($billToPlace !== '' ? ' - ' : '') . $billToPostal);
    }

    $companyInfo = bms_company_info();
    $fromName = trim((string) ($companyInfo['company_name'] ?? ''));
    if ($fromName === '') {
        $fromName = (string) (config('Email')->fromName ?? 'Billing Management System');
    }
    $fromNamePrint = function_exists('mb_strtoupper')
        ? mb_strtoupper($fromName, 'UTF-8')
        : strtoupper($fromName);
    $fromEmail = trim((string) ($companyInfo['email_id'] ?? ''));
    if ($fromEmail === '') {
        $fromEmail = (string) (config('Email')->fromEmail ?? '');
    }
    $fromPhone = trim((string) ($companyInfo['phone_number'] ?? ''));
    $fromWebsite = trim((string) ($companyInfo['website'] ?? ''));
    $fromWebsiteUrl = bms_company_website_url($fromWebsite);
    $fromAddress1 = trim((string) ($companyInfo['address_line1'] ?? ''));
    $fromAddress2 = trim((string) ($companyInfo['address_line2'] ?? ''));
    $fromCity = trim((string) ($companyInfo['city'] ?? ''));
    $fromState = trim((string) ($companyInfo['state'] ?? ''));
    $fromPincode = trim((string) ($companyInfo['pincode'] ?? ''));
    $fromGstin = trim((string) ($companyInfo['gstin_number'] ?? ''));
    $exportTaxReference = trim((string) ($companyInfo['export_tax_reference'] ?? ''));
    $currentAccountDetails = trim((string) ($companyInfo['current_account_details'] ?? ''));
    $paypalAccount = trim((string) ($companyInfo['paypal_account'] ?? ''));
    $taxReferenceLabel = $isExportInvoice ? 'LUT / IGST Ref' : 'GSTIN';
    $taxReferenceValue = $isExportInvoice ? $exportTaxReference : $fromGstin;
    $logoUrl = bms_company_logo_url($companyInfo);

    $fromPlace = trim(implode(', ', array_values(array_filter([$fromCity, $fromState]))));
    if ($fromPincode !== '') {
        $fromPlace = trim($fromPlace . ($fromPlace !== '' ? ' - ' : '') . $fromPincode);
    }

    $subTotal = (float) ($proforma['total_amount'] ?? 0);
    $netAmount = (float) (($proforma['net_amount'] ?? null) ?? $subTotal);
    $totalGst = (float) (($proforma['total_gst'] ?? null) ?? 0);
    $cgst = (float) (($proforma['cgst_amount'] ?? null) ?? 0);
    $sgst = (float) (($proforma['sgst_amount'] ?? null) ?? 0);
    $igst = (float) (($proforma['igst_amount'] ?? null) ?? 0);
    $gstPercent = (float) (($proforma['gst_percent'] ?? null) ?? 0);
    $billingFrom = trim((string) (($proforma['billing_from'] ?? '') ?: ''));
    $billingTo = trim((string) (($proforma['billing_to'] ?? '') ?: ''));
    $billingFromDisplay = $billingFrom !== '' ? $billingFrom : ($issueDate !== '' ? $issueDate : '-');
    $billingToDisplay = $billingTo !== '' ? $billingTo : ($issueDate !== '' ? $issueDate : '-');

    $exportNote = 'Supply meant for export under LUT without payment of IGST.';
?>

<style>
    .inv-sheet {
        width: 100%;
        max-width: 920px;
        margin: 0 auto;
        background: #fff;
        color: #000;
        border: 1px solid #87aeb2;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
    }
    .inv-toolbar {
        max-width: 920px;
        margin: 0 auto 12px;
    }
    .inv-header-title {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        letter-spacing: .04em;
        padding: 14px 20px 10px;
        text-transform: uppercase;
    }
    .inv-band {
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) 28px minmax(0, 0.92fr);
        border-top: 3px solid #87aeb2;
        border-bottom: 3px solid #87aeb2;
        align-items: stretch;
    }
    .inv-band-left,
    .inv-band-right {
        padding: 14px 16px;
        min-height: 190px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .inv-band-divider {
        background: #c9c9c9;
    }
    .inv-brand-row {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 14px;
    }
    .inv-logo {
        width: 82px;
        height: 82px;
        object-fit: contain;
        margin: 0;
        flex: 0 0 auto;
    }
    .inv-company-stack {
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
        align-items: flex-start;
    }
    .inv-company-name {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0;
        font-stretch: normal;
        line-height: 1.2;
        margin: 0;
        white-space: normal;
        display: block;
        max-width: 100%;
        padding: 4px 0;
        background: #d9e6eb;
        color: #1d4f6d;
        box-sizing: border-box;
        overflow-wrap: anywhere;
        font-synthesis: none;
    }
    .inv-company-details {
        display: grid;
        gap: 4px;
        justify-items: start;
    }
    .inv-company-line,
    .inv-meta-row,
    .inv-party-line {
        line-height: 1.45;
    }
    .inv-meta-grid {
        display: grid;
        grid-template-columns: 132px minmax(0, 1fr);
        gap: 6px 16px;
        align-content: start;
    }
    .inv-meta-label {
        font-weight: 700;
        line-height: 1.45;
    }
    .inv-items {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .inv-items th,
    .inv-items td {
        border: 1px solid #87aeb2;
        padding: 4px 6px;
        vertical-align: top;
    }
    .inv-items th {
        background: #eef4f4;
        font-weight: 700;
        text-align: center;
    }
    .inv-items .col-desc { width: 56%; }
    .inv-items .col-unit { width: 8%; }
    .inv-items .col-price { width: 12%; }
    .inv-items .col-qty { width: 12%; }
    .inv-items .col-amt { width: 12%; }
    .inv-desc-main {
        font-weight: 700;
        margin-bottom: 3px;
    }
    .inv-desc-extra {
        padding-left: 12px;
        font-size: 11px;
    }
    .inv-row-empty td {
        height: 28px;
    }
    .inv-right,
    .inv-center {
        text-align: right;
    }
    .inv-center {
        text-align: center;
    }
    .inv-summary-wrap {
        display: flex;
        justify-content: flex-end;
    }
    .inv-summary {
        width: 360px;
        border-left: 1px solid #87aeb2;
        border-right: 1px solid #87aeb2;
        border-bottom: 1px solid #87aeb2;
        border-collapse: collapse;
    }
    .inv-summary td {
        border-top: 1px solid #87aeb2;
        padding: 6px 8px;
    }
    .inv-summary .label {
        text-align: right;
        width: 65%;
    }
    .inv-summary .value {
        text-align: right;
        width: 35%;
        font-weight: 700;
    }
    .inv-total-row td {
        background: #e7f0ef;
        font-weight: 700;
    }
    .inv-footer {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 20px;
        padding: 16px 14px 10px;
        align-items: end;
    }
    .inv-footer-note {
        line-height: 1.5;
        min-height: 70px;
    }
    .inv-payment-details {
        margin-bottom: 10px;
    }
    .inv-payment-line {
        line-height: 1.45;
    }
    .inv-thanks {
        text-align: center;
        font-weight: 700;
        margin-top: 10px;
    }
    .inv-sign-block {
        text-align: center;
    }
    .inv-sign-company {
        margin-bottom: 46px;
    }
    .inv-sign-line {
        border-top: 1px solid #000;
        padding-top: 6px;
        font-size: 11px;
    }
    .inv-link {
        color: #0b76b7;
        text-decoration: underline;
    }
    @media print {
        body { background: #fff !important; }
        .inv-toolbar { display: none !important; }
        .inv-sheet {
            border: none;
            max-width: none;
        }
        .container { max-width: 100% !important; }
        @page { size: A4; margin: 8mm; }
    }
</style>

<div class="inv-toolbar d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <div class="d-flex gap-2">
        <a class="btn btn-sm btn-light" href="<?= base_url('proforma') ?>">Back</a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
    </div>
</div>

<div class="inv-sheet">
    <div class="inv-header-title"><?= esc($invoiceType) ?></div>

    <div class="inv-band">
        <div class="inv-band-left">
            <div class="inv-brand-row">
                <img src="<?= esc($logoUrl) ?>" alt="Company Logo" class="inv-logo">
                <div class="inv-company-stack">
                    <div class="inv-company-name"><?= esc($fromNamePrint) ?></div>
                </div>
            </div>
            <div class="inv-company-details">
                <?php if ($fromAddress1 !== ''): ?><div class="inv-company-line"><?= esc($fromAddress1) ?></div><?php endif; ?>
                <?php if ($fromAddress2 !== ''): ?><div class="inv-company-line"><?= esc($fromAddress2) ?></div><?php endif; ?>
                <?php if ($fromPlace !== ''): ?><div class="inv-company-line"><?= esc($fromPlace) ?></div><?php endif; ?>
                <?php if ($taxReferenceValue !== ''): ?><div class="inv-company-line"><?= esc($taxReferenceLabel) ?>: <?= esc($taxReferenceValue) ?></div><?php endif; ?>
                <?php if ($fromWebsite !== ''): ?><div class="inv-company-line"><a class="inv-link" href="<?= esc($fromWebsiteUrl) ?>" target="_blank" rel="noopener"><?= esc($fromWebsite) ?></a></div><?php endif; ?>
                <?php if ($fromEmail !== ''): ?><div class="inv-company-line"><a class="inv-link" href="mailto:<?= esc($fromEmail) ?>"><?= esc($fromEmail) ?></a></div><?php endif; ?>
                <?php if ($fromPhone !== ''): ?><div class="inv-company-line"><?= esc($fromPhone) ?></div><?php endif; ?>
            </div>
        </div>

        <div class="inv-band-divider"></div>

        <div class="inv-band-right">
            <div class="inv-meta-grid">
                <div class="inv-meta-label">Invoice No:</div>
                <div class="inv-meta-row"><?= esc($invoiceNo !== '' ? $invoiceNo : '-') ?></div>

                <div class="inv-meta-label">Inv Date:</div>
                <div class="inv-meta-row"><?= esc($issueDate !== '' ? $issueDate : '-') ?></div>

                <div class="inv-meta-label">Bill To:</div>
                <div class="inv-meta-row"><?= esc($billToName !== '' ? $billToName : $billToCompany) ?></div>

                <?php if (! $isExportInvoice): ?>
                    <div class="inv-meta-label">GST No:</div>
                    <div class="inv-meta-row"><?= esc($billToGst !== '' ? $billToGst : '-') ?></div>
                <?php endif; ?>

                <div class="inv-meta-label">Address:</div>
                <div class="inv-party-line">
                    <?php if ($billToAddress !== ''): ?><?= nl2br(esc($billToAddress)) ?><br><?php endif; ?>
                    <?= esc($billToPlace !== '' ? $billToPlace : '-') ?>
                </div>

                <?php if ($billToPhone !== ''): ?>
                    <div class="inv-meta-label">Phone:</div>
                    <div class="inv-meta-row"><?= esc($billToPhone) ?></div>
                <?php endif; ?>

                <?php if (! $isExportInvoice && $billToEmail !== ''): ?>
                    <div class="inv-meta-label">E-mail:</div>
                    <div class="inv-meta-row"><a class="inv-link" href="mailto:<?= esc($billToEmail) ?>"><?= esc($billToEmail) ?></a></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <table class="inv-items">
        <thead>
            <tr>
                <th class="col-desc">Description</th>
                <th class="col-unit">Unit</th>
                <th class="col-price">Price</th>
                <th class="col-qty">Quantity</th>
                <th class="col-amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($items ?? []) as $it): ?>
                <?php
                    $desc = bms_description_to_plain((string) ($it['description'] ?? ''));
                    $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $desc) ?: [])));
                    if ($lines === []) {
                        $lines = ['-'];
                    }
                ?>
                    <tr>
                        <td>
                            <?php foreach ($lines as $line): ?>
                                <div class="inv-desc-extra"><?= esc('• ' . $line) ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td class="inv-center">Nos</td>
                        <td class="inv-right"><?= $formatMoney((float) ($it['unit_price'] ?? 0)) ?></td>
                        <td class="inv-center"><?= esc(rtrim(rtrim(number_format((float) ($it['quantity'] ?? 0), 2, '.', ''), '0'), '.')) ?></td>
                        <td class="inv-right"><?= $formatMoney((float) ($it['amount'] ?? 0)) ?></td>
                    </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="inv-summary-wrap">
        <table class="inv-summary">
            <tbody>
                <tr>
                    <td class="label"><?= esc($isExportInvoice ? 'Invoice Total' : ('Invoice Total - ' . $currency)) ?></td>
                    <td class="value"><?= $formatMoney($subTotal) ?></td>
                </tr>
                <?php if ($isExportInvoice): ?>
                    <tr>
                        <td class="label"><?= esc($taxReferenceLabel) ?></td>
                        <td class="value"><?= esc($taxReferenceValue !== '' ? $taxReferenceValue : 'No GST') ?></td>
                    </tr>
                <?php else: ?>
                    <?php if ($igst > 0): ?>
                        <tr>
                            <td class="label">IGST - <?= esc(number_format($gstPercent, 2)) ?>%</td>
                            <td class="value"><?= $formatMoney($igst) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td class="label">CGST - <?= esc(number_format($gstPercent / 2, 2)) ?>%</td>
                            <td class="value"><?= $formatMoney($cgst) ?></td>
                        </tr>
                        <tr>
                            <td class="label">SGST - <?= esc(number_format($gstPercent / 2, 2)) ?>%</td>
                            <td class="value"><?= $formatMoney($sgst) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
                <tr class="inv-total-row">
                    <td class="label"><?= esc($isExportInvoice ? 'Total Amount' : 'Total Amount Receivable') ?></td>
                    <td class="value"><?= $formatMoney($netAmount) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="inv-footer">
        <div class="inv-footer-note">
            <?php if ($currentAccountDetails !== '' || $paypalAccount !== ''): ?>
                <div class="inv-payment-details">
                    <?php if ($currentAccountDetails !== ''): ?><div class="inv-payment-line"><?= esc($currentAccountDetails) ?></div><?php endif; ?>
                    <?php if ($paypalAccount !== ''): ?><div class="inv-payment-line">Paypal account: <?= esc($paypalAccount) ?></div><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($isExportInvoice): ?>
                <div><?= esc($exportNote) ?></div>
            <?php else: ?>
                <div>For invoice support, contact <?= esc($fromEmail !== '' ? $fromEmail : $fromName) ?><?= $fromPhone !== '' ? (' / ' . esc($fromPhone)) : '' ?>.</div>
            <?php endif; ?>
            <?php if ($billingFrom !== '' || $billingTo !== ''): ?>
                <div>Billing Period: <?= esc($billingFromDisplay) ?> to <?= esc($billingToDisplay) ?></div>
            <?php endif; ?>
            <div class="inv-thanks">Thank you for your business.!</div>
        </div>

        <div class="inv-sign-block">
            <div class="inv-sign-company">For <?= esc($fromName) ?></div>
            <div class="inv-sign-line">Authorized Signature</div>
        </div>
    </div>
</div>

<?php if (! empty($autoprint)): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () { window.print(); }, 250);
        });
    </script>
<?php endif; ?>
<?= $this->endSection() ?>
