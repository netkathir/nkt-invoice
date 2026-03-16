<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Create Daily Expense Entry</h5>
    <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('day-book/daily-expense-form') ?>">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= base_url('day-book/daily-expense-form/store') ?>" enctype="multipart/form-data" class="row g-3">
            <?= csrf_field() ?>

            <div class="col-12 col-lg-3">
                <label class="form-label">Expense ID</label>
                <input type="text" class="form-control" name="expense_code" value="<?= esc(old('expense_code')) ?>" placeholder="Auto Generated if left empty">
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <div class="input-group bms-date-wrap">
                    <input type="text" class="form-control" id="de_exp_date" placeholder="DD/MM/YYYY" autocomplete="off" required>
                    <button class="btn btn-outline-secondary bms-date-btn" type="button" aria-label="Pick date">
                        <span aria-hidden="true">📅</span>
                    </button>
                    <input
                        type="date"
                        class="bms-native-date"
                        id="de_exp_date_native"
                        name="expense_date"
                        value="<?= esc(old('expense_date') ?: date('Y-m-d')) ?>"
                        tabindex="-1"
                        aria-hidden="true"
                        required
                    >
                </div>
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Expense Category <span class="text-danger">*</span></label>
                <select class="form-select" name="category" required>
                    <?php $cat = (string) (old('category') ?? ''); ?>
                    <option value="">-- Select Category --</option>
                    <option value="Transportation" <?= $cat === 'Transportation' ? 'selected' : '' ?>>Transportation</option>
                    <option value="Food" <?= $cat === 'Food' ? 'selected' : '' ?>>Food</option>
                    <option value="Office" <?= $cat === 'Office' ? 'selected' : '' ?>>Office</option>
                    <option value="Maintenance" <?= $cat === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="Misc" <?= $cat === 'Misc' ? 'selected' : '' ?>>Misc</option>
                </select>
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" name="description" value="<?= esc(old('description')) ?>" placeholder="e.g., Printer toner, Taxi fare">
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" min="0" step="0.01" class="form-control" name="amount" value="<?= esc(old('amount')) ?>" required>
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                <?php $pm = (string) (old('payment_method') ?? 'Cash'); ?>
                <select class="form-select" name="payment_method" required>
                    <option value="">-- Select Payment Method --</option>
                    <option value="Cash" <?= $pm === 'Cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="UPI" <?= $pm === 'UPI' ? 'selected' : '' ?>>UPI</option>
                    <option value="Bank Transfer" <?= $pm === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="Cheque" <?= $pm === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                </select>
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Paid To <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="paid_to" value="<?= esc(old('paid_to')) ?>" placeholder="e.g., John Doe, XYZ Suppliers" required>
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Receipt</label>
                <input type="file" class="form-control" name="receipt" accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">Accepted formats: PDF, JPG, PNG (Max 5MB)</div>
            </div>

            <div class="col-12">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" rows="3" placeholder="Additional remarks or comments (optional)"><?= esc(old('remarks')) ?></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a class="btn btn-secondary" href="<?= base_url('day-book/daily-expense-form') ?>">Cancel</a>
                <button class="btn btn-primary" type="submit">Save Daily Expense Entry</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initDailyExpenseEntry && window.BMS.initDailyExpenseEntry();
    });
</script>
<?= $this->endSection() ?>
