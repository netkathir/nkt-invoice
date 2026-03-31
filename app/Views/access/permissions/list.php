<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h4 class="bms-list-title mb-0">Permissions (Forms)</h4>
                    <p class="bms-list-subtitle mb-0">Manage form-level access entries and keep permission mapping organized in a shared admin list style.</p>
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
        <div class="bms-list-panel-head">
            <div>
                <div class="bms-list-panel-title">Listing View</div>
                <div class="bms-list-panel-text">Browse form permissions and edit access definitions from a single structured register.</div>
            </div>
        </div>
        <div class="card-body pt-0">
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


