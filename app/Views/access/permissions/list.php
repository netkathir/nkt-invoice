<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Permissions</h5>
    <?php if (can('permissions.create')): ?>
        <button class="btn btn-primary" id="btnAddPermission" type="button">Add Permission</button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtPermissions" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>Key</th>
                <th>Label</th>
                <th>Module</th>
                <th>Roles</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
        <div class="small text-muted mt-2">
            Tip: Keep permission keys consistent (e.g., <code>roles.view</code>, <code>billable_items.create</code>).
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initPermissions && window.BMS.initPermissions({
            canCreate: <?= can('permissions.create') ? 'true' : 'false' ?>,
            canEdit: <?= can('permissions.edit') ? 'true' : 'false' ?>,
            canDelete: <?= can('permissions.delete') ? 'true' : 'false' ?>,
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('access/permissions/form') ?>
<?= $this->endSection() ?>

