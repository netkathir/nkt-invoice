<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h4 class="bms-list-title mb-0">Users</h4>
                </div>
                <div class="bms-list-actions">
                    <?php if (! empty($canCreate)): ?>
                        <button class="btn btn-primary" id="btnAddUser" type="button">Add New User</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-panel border-0">
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


