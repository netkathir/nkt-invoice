<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h4 class="bms-list-title mb-0">Role Permissions</h4>
                    <p class="bms-list-subtitle mb-0">Inspect role descriptions, mapped permissions, and assignment actions from one centralized permission matrix.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-panel border-0">
        <div class="bms-list-panel-head">
            <div>
                <div class="bms-list-panel-title">Listing View</div>
                <div class="bms-list-panel-text">See each role and its effective permissions in the same list-view system used across the app.</div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="dtRolePermissions" class="table table-striped table-bordered nowrap w-100">
                <thead>
                <tr>
                    <th class="text-center">S.No</th>
                    <th class="text-center">Role Name</th>
                    <th class="text-center">Description</th>
                    <th class="text-center">Permissions</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initRolePermissionsList && window.BMS.initRolePermissionsList({});
    });
</script>
<?= $this->endSection() ?>

