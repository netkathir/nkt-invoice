<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $isSuper = authz()->isSuperAdmin(); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Roles</h5>
    <?php if (can('roles.create')): ?>
        <button class="btn btn-primary" id="btnAddRole" type="button">Add Role</button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtRoles" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Description</th>
                <th class="text-end" style="text-align: left !important">Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initRoles && window.BMS.initRoles({
            isSuper: <?= $isSuper ? 'true' : 'false' ?>,
            canCreate: <?= can('roles.create') ? 'true' : 'false' ?>,
            canEdit: <?= can('roles.edit') ? 'true' : 'false' ?>,
            canDelete: <?= can('roles.delete') ? 'true' : 'false' ?>,
            canAssignPerms: <?= can('roles.assign_perms') ? 'true' : 'false' ?>,
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('access/roles/form') ?>
<?= $this->endSection() ?>

