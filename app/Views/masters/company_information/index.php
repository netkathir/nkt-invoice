<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $company = $company ?? [];
    $errors = is_array($errors ?? null) ? $errors : [];
    $oldOr = static function (string $key, string $fallback = '') use ($company): string {
        $old = old($key);
        if ($old !== null) {
            return (string) $old;
        }
        return (string) ($company[$key] ?? $fallback);
    };
    $currentAccountRaw = trim((string) ($company['current_account_details'] ?? ''));
    $currentAccountIfsc = '';
    if (preg_match('/IFSC\s*:?\s*([A-Z0-9]+)/i', $currentAccountRaw, $ifscMatch) === 1) {
        $currentAccountIfsc = strtoupper((string) ($ifscMatch[1] ?? ''));
    }
    $currentAccountValue = preg_replace('/\s*IFSC\s*:?\s*[A-Z0-9]+/i', '', $currentAccountRaw) ?? $currentAccountRaw;
    $currentAccountValue = preg_replace('/^Current\s*A\/C\s*[-:]?\s*/i', '', trim($currentAccountValue)) ?? trim($currentAccountValue);
    $currentAccountValue = trim($currentAccountValue);
    $currentAccountValueInput = old('current_account_value');
    if ($currentAccountValueInput === null || $currentAccountValueInput === '') {
        $currentAccountValueInput = $currentAccountValue !== '' ? $currentAccountValue : '623305034567';
    }
    $currentAccountIfscInput = old('current_account_ifsc');
    if ($currentAccountIfscInput === null || $currentAccountIfscInput === '') {
        $currentAccountIfscInput = $currentAccountIfsc !== '' ? $currentAccountIfsc : 'ICIC0006233';
    }
    $logoUrl = bms_company_logo_url($company);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Company Information</h5>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= base_url('company-information/save') ?>" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= isset($errors['company_name']) ? ' is-invalid' : '' ?>" name="company_name" value="<?= esc($oldOr('company_name')) ?>" required>
                            <?php if (isset($errors['company_name'])): ?><div class="invalid-feedback"><?= esc((string) $errors['company_name']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Company Logo</label>
                            <input type="file" class="form-control<?= isset($errors['company_logo']) ? ' is-invalid' : '' ?>" name="company_logo" accept=".png,.jpg,.jpeg">
                            <div class="form-text">PNG/JPEG format, max 1MB</div>
                            <?php if (isset($errors['company_logo'])): ?><div class="invalid-feedback d-block"><?= esc((string) $errors['company_logo']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <div class="fw-semibold mb-2">Address</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= isset($errors['address_line1']) ? ' is-invalid' : '' ?>" name="address_line1" value="<?= esc($oldOr('address_line1')) ?>" required>
                            <?php if (isset($errors['address_line1'])): ?><div class="invalid-feedback"><?= esc((string) $errors['address_line1']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Address Line 2 (Optional)</label>
                            <input type="text" class="form-control<?= isset($errors['address_line2']) ? ' is-invalid' : '' ?>" name="address_line2" value="<?= esc($oldOr('address_line2')) ?>">
                            <?php if (isset($errors['address_line2'])): ?><div class="invalid-feedback"><?= esc((string) $errors['address_line2']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= isset($errors['city']) ? ' is-invalid' : '' ?>" name="city" value="<?= esc($oldOr('city')) ?>" required>
                            <?php if (isset($errors['city'])): ?><div class="invalid-feedback"><?= esc((string) $errors['city']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= isset($errors['state']) ? ' is-invalid' : '' ?>" name="state" value="<?= esc($oldOr('state')) ?>" required>
                            <?php if (isset($errors['state'])): ?><div class="invalid-feedback"><?= esc((string) $errors['state']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= isset($errors['pincode']) ? ' is-invalid' : '' ?>" name="pincode" value="<?= esc($oldOr('pincode')) ?>" required>
                            <?php if (isset($errors['pincode'])): ?><div class="invalid-feedback"><?= esc((string) $errors['pincode']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label">GSTIN Number (India) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= isset($errors['gstin_number']) ? ' is-invalid' : '' ?>" name="gstin_number" value="<?= esc($oldOr('gstin_number')) ?>" maxlength="15" required>
                            <?php if (isset($errors['gstin_number'])): ?><div class="invalid-feedback"><?= esc((string) $errors['gstin_number']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label">LUT / IGST Reference (Other Countries)</label>
                            <input type="text" class="form-control<?= isset($errors['export_tax_reference']) ? ' is-invalid' : '' ?>" name="export_tax_reference" value="<?= esc($oldOr('export_tax_reference')) ?>" maxlength="191" placeholder="Optional">
                            <div class="form-text">Used on export invoices generated without GST.</div>
                            <?php if (isset($errors['export_tax_reference'])): ?><div class="invalid-feedback"><?= esc((string) $errors['export_tax_reference']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Email ID</label>
                            <input type="email" class="form-control<?= isset($errors['email_id']) ? ' is-invalid' : '' ?>" name="email_id" value="<?= esc($oldOr('email_id')) ?>">
                            <?php if (isset($errors['email_id'])): ?><div class="invalid-feedback"><?= esc((string) $errors['email_id']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Website</label>
                            <input type="text" class="form-control<?= isset($errors['website']) ? ' is-invalid' : '' ?>" name="website" value="<?= esc($oldOr('website')) ?>" placeholder="example.com">
                            <?php if (isset($errors['website'])): ?><div class="invalid-feedback"><?= esc((string) $errors['website']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control<?= isset($errors['phone_number']) ? ' is-invalid' : '' ?>" name="phone_number" value="<?= esc($oldOr('phone_number')) ?>">
                            <?php if (isset($errors['phone_number'])): ?><div class="invalid-feedback"><?= esc((string) $errors['phone_number']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account Details</label>
                            <input type="text" class="form-control<?= isset($errors['current_account_details']) ? ' is-invalid' : '' ?>" name="current_account_value" value="<?= esc((string) $currentAccountValueInput) ?>" placeholder="623305034567">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" class="form-control<?= isset($errors['current_account_details']) ? ' is-invalid' : '' ?>" name="current_account_ifsc" value="<?= esc((string) $currentAccountIfscInput) ?>" placeholder="ICIC0006233">
                            <?php if (isset($errors['current_account_details'])): ?><div class="invalid-feedback d-block"><?= esc((string) $errors['current_account_details']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">PayPal Account</label>
                            <input type="email" class="form-control<?= isset($errors['paypal_account']) ? ' is-invalid' : '' ?>" name="paypal_account" value="<?= esc($oldOr('paypal_account', 'maraimani@netkathir.com')) ?>">
                            <?php if (isset($errors['paypal_account'])): ?><div class="invalid-feedback"><?= esc((string) $errors['paypal_account']) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="border rounded-3 p-3 bg-body-tertiary h-100">
                        <div class="fw-semibold mb-2">Logo Preview</div>
                        <div class="border rounded-3 bg-white p-3 text-center">
                            <img src="<?= esc($logoUrl) ?>" alt="Company Logo" style="max-width:100%; max-height:160px; object-fit:contain;">
                        </div>
                        <div class="small text-muted mt-2">Saved company information will be used in India GST invoices and export/LUT invoice templates.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Save Company Information</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
