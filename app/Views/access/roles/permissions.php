<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-1">Assign Permissions</h5>
        <div class="text-muted small">Role: <strong><?= esc((string) ($role['name'] ?? '')) ?></strong></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= base_url('roles') ?>">Back</a>
        <?php if (! $isSuperRole && can('roles.assign_perms')): ?>
            <button class="btn btn-primary" id="btnSaveRolePerms" type="button">Save</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($isSuperRole): ?>
    <div class="alert alert-info">
        Super Admin role always has full access. Permissions for this role are locked to prevent accidental changes.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form id="rolePermsForm">
            <div class="accordion" id="permAccordion">
                <?php $i = 0; foreach ($byModule as $module => $perms): $i++; ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $i ?>">
                            <button class="accordion-button <?= $i === 1 ? '' : 'collapsed' ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>"
                                    aria-expanded="<?= $i === 1 ? 'true' : 'false' ?>" aria-controls="collapse<?= $i ?>">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span><?= esc($module) ?></span>
                                    <?php if (! $isSuperRole): ?>
                                        <span class="ms-2">
                                            <input class="form-check-input me-1 module-select-all" type="checkbox" data-module="<?= esc($module) ?>" id="modAll<?= $i ?>">
                                            <label class="form-check-label small" for="modAll<?= $i ?>">Select all</label>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i === 1 ? 'show' : '' ?>" aria-labelledby="heading<?= $i ?>" data-bs-parent="#permAccordion">
                            <div class="accordion-body">
                                <div class="row g-2">
                                    <?php foreach ($perms as $p): ?>
                                        <?php $pid = (int) ($p['id'] ?? 0); ?>
                                        <div class="col-12 col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input perm-checkbox"
                                                       type="checkbox"
                                                       value="<?= esc((string) $pid) ?>"
                                                       name="permission_ids[]"
                                                       data-module="<?= esc($module) ?>"
                                                       id="perm<?= $pid ?>"
                                                    <?= isset($selected[$pid]) ? 'checked' : '' ?>
                                                    <?= $isSuperRole ? 'disabled' : '' ?>>
                                                <label class="form-check-label" for="perm<?= $pid ?>">
                                                    <div class="fw-semibold"><?= esc((string) ($p['label'] ?? $p['key'] ?? '')) ?></div>
                                                    <div class="text-muted small"><?= esc((string) ($p['key'] ?? '')) ?></div>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
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
        window.BMS.initRolePermissions && window.BMS.initRolePermissions({
            roleId: <?= (int) ($role['id'] ?? 0) ?>,
            locked: <?= $isSuperRole ? 'true' : 'false' ?>
        });
    });
</script>
<?= $this->endSection() ?>

