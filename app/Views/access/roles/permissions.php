<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $roleId = (int) ($role['id'] ?? 0);
    $roleName = (string) ($role['name'] ?? '');
    $roleDesc = (string) ($role['description'] ?? '');
    $roleLabel = trim($roleName . ($roleDesc !== '' ? (' - ' . $roleDesc) : ''));

    $missingPages = [];
    foreach (($byModulePages ?? []) as $module => $pages) {
        foreach (($pages ?? []) as $pageKey => $row) {
            $readIds = array_values(array_unique(array_map('intval', (array) ($row['read'] ?? []))));
            $writeIds = array_values(array_unique(array_map('intval', (array) ($row['write'] ?? []))));
            $deleteIds = array_values(array_unique(array_map('intval', (array) ($row['delete'] ?? []))));
            $allIds = array_values(array_unique(array_filter(array_merge($readIds, $writeIds, $deleteIds))));
            if ($allIds === []) {
                $missingPages[] = (string) ($row['label'] ?? $pageKey);
            }
        }
    }
?>

<div class="mb-3">
    <h4 class="mb-1">Assign Permissions to Role</h4>
    <div class="text-muted small">Select a role and assign permissions</div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <label class="form-label">Select Role <span class="text-danger">*</span></label>
        <select class="form-select" id="rpRoleSelect" <?= $isSuperRole ? 'disabled' : '' ?>>
            <?php foreach (($rolesList ?? []) as $r): ?>
                <?php
                    $rid = (int) ($r['id'] ?? 0);
                    $rn = (string) ($r['name'] ?? '');
                    $rd = (string) ($r['description'] ?? '');
                    $lbl = trim($rn . ($rd !== '' ? (' - ' . $rd) : ''));
                ?>
                <option value="<?= $rid ?>" <?= $rid === $roleId ? 'selected' : '' ?>>
                    <?= esc($lbl !== '' ? $lbl : $rn) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="alert alert-info mt-3 mb-0">
            <strong>Selected Role:</strong> <?= esc($roleLabel !== '' ? $roleLabel : $roleName) ?>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="fw-semibold mb-2">Permission Types Explained:</div>
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="border rounded p-3 h-100 border-primary">
                    <div class="fw-semibold text-primary mb-2">Read (View)</div>
                    <div class="small text-muted mb-2">View only. User can view the list and details but cannot make any changes (no editing, adding, or deleting).</div>
                    <div class="small">
                        <div>&#10003; View list</div>
                        <div>&#10003; View details</div>
                        <div>&#10007; Cannot edit</div>
                        <div>&#10007; Cannot add</div>
                        <div>&#10007; Cannot delete</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="border rounded p-3 h-100 border-success">
                    <div class="fw-semibold text-success mb-2">Write (Edit/Add)</div>
                    <div class="small text-muted mb-2">Edit or Add. User can edit existing records or add new ones but cannot delete existing records.</div>
                    <div class="small">
                        <div>&#10003; View list</div>
                        <div>&#10003; View details</div>
                        <div>&#10003; Edit records</div>
                        <div>&#10003; Add new records</div>
                        <div>&#10007; Cannot delete</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="border rounded p-3 h-100 border-danger">
                    <div class="fw-semibold text-danger mb-2">Delete (Full Access)</div>
                    <div class="small text-muted mb-2">Full control. User can Read, Edit, Add, and Delete. This is the most powerful permission, giving complete control.</div>
                    <div class="small">
                        <div>&#10003; View list</div>
                        <div>&#10003; View details</div>
                        <div>&#10003; Edit records</div>
                        <div>&#10003; Add new records</div>
                        <div>&#10003; Delete records</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-3 mb-0 small">
            <strong>Note:</strong> Permissions follow a hierarchy. Selecting <strong>Write</strong> automatically includes <strong>Read</strong>. Selecting <strong>Delete</strong> automatically includes both <strong>Read</strong> and <strong>Write</strong>. Unchecking a lower-level permission will also uncheck higher level permissions.
        </div>
    </div>
</div>

<?php if ($isSuperRole): ?>
    <div class="alert alert-info">
        Super Admin role always has full access. Permissions for this role are locked to prevent accidental changes.
    </div>
<?php endif; ?>

<?php if ($missingPages !== []): ?>
    <div class="alert alert-warning">
        Some permission keys are missing in the database, so those rows are disabled: <strong><?= esc(implode(', ', $missingPages)) ?></strong>.
        Run <code>php spark migrate --force</code> to seed them.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form id="rolePermsForm">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Page</th>
                        <th class="text-center" style="width:140px;">Read</th>
                        <th class="text-center" style="width:180px;">Add / Edit / Update</th>
                        <th class="text-center" style="width:140px;">Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (($byModulePages ?? []) as $module => $pages): ?>
                        <tr class="table-secondary">
                            <td colspan="4" class="fw-semibold text-uppercase small"><?= esc((string) $module) ?></td>
                        </tr>
                        <?php foreach ($pages as $pageKey => $row): ?>
                            <?php
                                $readIds = array_values(array_unique(array_map('intval', (array) ($row['read'] ?? []))));
                                $writeIds = array_values(array_unique(array_map('intval', (array) ($row['write'] ?? []))));
                                $deleteIds = array_values(array_unique(array_map('intval', (array) ($row['delete'] ?? []))));
                                $allIds = array_values(array_unique(array_filter(array_merge($readIds, $writeIds, $deleteIds))));
                                $rowDisabled = $isSuperRole || ($allIds === []);
                            ?>
                            <tr data-page="<?= esc((string) $pageKey) ?>">
                                <td class="fw-medium">
                                    <?= esc((string) ($row['label'] ?? $pageKey)) ?>
                                    <?php if ($allIds === []): ?>
                                        <span class="badge text-bg-secondary ms-2">Not seeded</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <input class="form-check-input perm-level" type="checkbox" data-level="read" data-ids="<?= esc(implode(',', $readIds)) ?>" <?= $rowDisabled ? 'disabled' : '' ?>>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input perm-level" type="checkbox" data-level="write" data-ids="<?= esc(implode(',', $writeIds)) ?>" <?= $rowDisabled ? 'disabled' : '' ?>>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input perm-level" type="checkbox" data-level="delete" data-ids="<?= esc(implode(',', $deleteIds)) ?>" <?= $rowDisabled ? 'disabled' : '' ?>>
                                </td>

                                <?php foreach ($allIds as $pid): ?>
                                    <input class="perm-id d-none" type="checkbox" name="permission_ids[]" value="<?= (int) $pid ?>" <?= isset($selected[(int) $pid]) ? 'checked' : '' ?>>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-3">
    <?php if (! $isSuperRole && can('roles.assign_perms')): ?>
        <button class="btn btn-success" id="btnSaveRolePerms" type="button">Save Permissions</button>
    <?php endif; ?>
    <a class="btn btn-secondary" href="<?= base_url('role-permissions') ?>">Back to List</a>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initRolePermissions && window.BMS.initRolePermissions({
            roleId: <?= $roleId ?>,
            locked: <?= $isSuperRole ? 'true' : 'false' ?>
        });

        const sel = document.getElementById('rpRoleSelect');
        if (sel) {
            sel.addEventListener('change', function () {
                const id = parseInt(sel.value || '0', 10) || 0;
                if (!id) return;
                window.location.href = <?= json_encode(base_url('roles/')) ?> + id + '/permissions';
            });
        }
    });
</script>
<?= $this->endSection() ?>
