<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="mb-0">Users</h4>
    <?php if (! empty($canCreate)): ?>
        <button class="btn btn-primary" id="btnAddUser" type="button">
            <span class="me-1" aria-hidden="true">+</span>
            Add New User
        </button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtUsers" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Role</th>
                <th>Status</th>
                <th class="text-end" style="text-align: left !important">Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initUsers && window.BMS.initUsers({
            canCreate: <?= ! empty($canCreate) ? 'true' : 'false' ?>,
            canEdit: <?= ! empty($canEdit) ? 'true' : 'false' ?>,
            canDelete: <?= ! empty($canDelete) ? 'true' : 'false' ?>,
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('access/users/form') ?>
<?= $this->endSection() ?>

