<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">View Invoice</h5>
    <a class="btn btn-light" href="<?= base_url('proforma') ?>">Back</a>
</div>

<?php
    $currency = (string) (($proforma['currency'] ?? '') ?: 'INR');
    $invoiceType = (string) (($proforma['invoice_type'] ?? '') ?: 'GST Invoice');
    $gstPercent = (float) ($proforma['gst_percent'] ?? 0);
    $gstMode = (string) (($proforma['gst_mode'] ?? '') ?: 'CGST_SGST');

    $companyName = (string) (($proforma['client_name'] ?? '') ?: '');
    $customerName = (string) (($proforma['contact_person'] ?? '') ?: $companyName);
    $addr1 = (string) (($proforma['address'] ?? '') ?: '');
    $addr2 = (string) (($proforma['billing_address'] ?? '') ?: '');
    $city = (string) (($proforma['city'] ?? '') ?: '');
    $state = (string) (($proforma['state'] ?? '') ?: '');
    $pincode = (string) (($proforma['postal_code'] ?? '') ?: '');

    $totalAmount = (float) ($proforma['total_amount'] ?? 0);
    $totalGst = (float) ($proforma['total_gst'] ?? 0);
    $netAmount = (float) (($proforma['net_amount'] ?? null) ?? ($totalAmount + $totalGst));
?>

<div class="card bms-invoice-card">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="flex-shrink-0">
                <img src="<?= base_url('favicon_netk.png') ?>" alt="Company Logo" class="bms-invoice-logo">
            </div>
            <div class="flex-grow-1">
                <div class="h5 mb-1">Invoice</div>
                <div class="text-muted small">Invoice</div>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Invoice No</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc((string) ($proforma['proforma_number'] ?? '')) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Customer Name</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc($customerName) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">GST NO</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Address Line2</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc($addr2) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">State</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc($state) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Currency</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <select class="form-select" disabled>
                            <option value="INR" <?= $currency === 'INR' ? 'selected' : '' ?>>INR</option>
                            <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD</option>
                            <option value="EUR" <?= $currency === 'EUR' ? 'selected' : '' ?>>EUR</option>
                            <option value="GBP" <?= $currency === 'GBP' ? 'selected' : '' ?>>GBP</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Invoice Type</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <select class="form-select" disabled>
                            <option value="GST Invoice" <?= $invoiceType === 'GST Invoice' ? 'selected' : '' ?>>GST Invoice</option>
                            <option value="Export Invoice" <?= $invoiceType === 'Export Invoice' ? 'selected' : '' ?>>Export Invoice</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Company Name</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc($companyName) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Date Of Issue</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc((string) ($proforma['proforma_date'] ?? '')) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">Address Line1</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc($addr1) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">City</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc($city) ?>" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0 text-md-end fw-semibold">PinCode</label>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="text" class="form-control" value="<?= esc($pincode) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 bms-items-box">
            <div class="fw-semibold mb-2">Items</div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle nowrap w-100 mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width:200px;">Item</th>
                        <th>Item Description</th>
                        <th class="text-end" style="width:120px;">Quantity</th>
                        <th style="width:110px;">UOM</th>
                        <th class="text-end" style="width:140px;">Price</th>
                        <th class="text-end" style="width:140px;">Value</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (($items ?? []) as $it): ?>
                        <?php
                            $desc = (string) ($it['description'] ?? '');
                            $lines = array_values(array_filter(array_map('trim', preg_split('/\\r?\\n/', $desc) ?: [])));
                            $itemName = $lines[0] ?? '';
                            $rest = $lines;
                            if ($rest !== []) {
                                array_shift($rest);
                            }
                            $descText = implode("\n", $rest);
                        ?>
                        <tr>
                            <td><input type="text" class="form-control form-control-sm" value="<?= esc($itemName) ?>" readonly></td>
                            <td><textarea rows="1" class="form-control form-control-sm" readonly><?= esc($descText) ?></textarea></td>
                            <td><input type="text" class="form-control form-control-sm text-end" value="<?= esc(number_format((float) ($it['quantity'] ?? 0), 2, '.', '')) ?>" readonly></td>
                            <td><input type="text" class="form-control form-control-sm" value="Nos" readonly></td>
                            <td><input type="text" class="form-control form-control-sm text-end" value="<?= esc(number_format((float) ($it['unit_price'] ?? 0), 2, '.', '')) ?>" readonly></td>
                            <td><input type="text" class="form-control form-control-sm text-end" value="<?= esc(number_format((float) ($it['amount'] ?? 0), 2, '.', '')) ?>" readonly></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (($items ?? []) === []): ?>
                        <tr><td colspan="6" class="text-center text-muted">No items.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-3 justify-content-end mt-4">
            <div class="col-12 col-md-6 col-lg-5 col-xl-4">
                <div class="row g-2 align-items-center">
                    <div class="col-4 text-end fw-semibold">Total</div>
                    <div class="col-8">
                        <input type="text" class="form-control" value="<?= esc(number_format($totalAmount, 2, '.', '')) ?>" readonly>
                    </div>
                </div>

                <div class="mt-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-4 text-end fw-semibold">GST %</div>
                        <div class="col-8">
                            <input type="text" class="form-control" value="<?= esc(number_format($gstPercent, 2, '.', '')) ?>" readonly>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-1">
                        <div class="col-4"></div>
                        <div class="col-8 d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" <?= $gstMode === 'IGST' ? '' : 'checked' ?> disabled>
                                <label class="form-check-label">CGST &amp; SGST</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" <?= $gstMode === 'IGST' ? 'checked' : '' ?> disabled>
                                <label class="form-check-label">IGST</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-2">
                        <div class="col-4 text-end fw-semibold">Total GST</div>
                        <div class="col-8">
                            <input type="text" class="form-control" value="<?= esc(number_format($totalGst, 2, '.', '')) ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row g-2 align-items-center mt-2">
                    <div class="col-4 text-end fw-semibold">Net Amount</div>
                    <div class="col-8">
                        <input type="text" class="form-control" value="<?= esc(number_format($netAmount, 2, '.', '')) ?>" readonly>
                    </div>
                </div>

                <div class="row g-2 align-items-center mt-2">
                    <div class="col-4 text-end fw-semibold">Due Date</div>
                    <div class="col-8">
                        <input type="text" class="form-control" value="<?= esc((string) (($proforma['billing_to'] ?? '') ?: '')) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>