<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="mb-0">Permissions (Forms)</h4>
    <?php if (can('permissions.create')): ?>
        <button class="btn btn-primary" id="btnAddPermission" type="button">
            <span class="me-1" aria-hidden="true">+</span>
            Add Form
        </button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtPermissions" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>S.No</th>
                <th>Form Name</th>
                <th class="text-end" style="text-align: left !important">Actions</th>
            </tr>
            </thead>
        </table>
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

