<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-1">Assign Roles</h5>
        <div class="text-muted small">
            Admin: <strong><?= esc((string) ($admin['name'] ?? '')) ?></strong>
            (<?= esc((string) ($admin['username'] ?? '')) ?>)
        </div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= base_url('admin-roles') ?>">Back</a>
        <button class="btn btn-primary" id="btnSaveAdminRoles" type="button">Save</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form id="adminRolesForm">
            <div class="row g-2">
                <?php foreach ($roles as $r): ?>
                    <?php $rid = (int) ($r['id'] ?? 0); ?>
                    <?php $isSuper = (int) ($r['is_super'] ?? 0) === 1; ?>
                    <div class="col-12 col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= esc((string) $rid) ?>" id="role<?= $rid ?>"
                                <?= isset($selected[$rid]) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role<?= $rid ?>">
                                <div class="fw-semibold">
                                    <?= esc((string) ($r['name'] ?? '')) ?>
                                    <?php if ($isSuper): ?>
                                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle ms-1">Super</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (! empty($r['description'])): ?>
                                    <div class="text-muted small"><?= esc((string) $r['description']) ?></div>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initAdminRolesEdit && window.BMS.initAdminRolesEdit({
            adminId: <?= (int) ($admin['id'] ?? 0) ?>,
        });
    });
</script>
<?= $this->endSection() ?>
