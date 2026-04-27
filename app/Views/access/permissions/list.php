<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h4 class="bms-list-title mb-0">Permissions (Forms)</h4>
                </div>
                <div class="bms-list-actions">
                    <?php if (can('permissions.create')): ?>
                        <button class="btn btn-primary" id="btnAddPermission" type="button">Add Form</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-panel border-0">
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


