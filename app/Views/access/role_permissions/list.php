<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="mb-0">Role Permissions</h4>
</div>

<div class="card">
    <div class="card-body">
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

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initRolePermissionsList && window.BMS.initRolePermissionsList({});
    });
</script>
<?= $this->endSection() ?>
