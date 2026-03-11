<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Clients</h5>
    <button class="btn btn-primary" id="btnAddClient" type="button">Add Client</button>
</div>

<div class="card">
    <div class="card-body">
        <table id="dtClients" class="table table-striped table-bordered nowrap w-100">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initClients && window.BMS.initClients();
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('masters/client_master/form') ?>
<?= $this->endSection() ?>
