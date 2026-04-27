<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $isSuper = authz()->isSuperAdmin(); ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h5 class="bms-list-title mb-0">Roles</h5>
                </div>
                <div class="bms-list-actions">
                    <?php if (can('roles.create')): ?>
                        <button class="btn btn-primary" id="btnAddRole" type="button">Add Role</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-panel border-0">
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


