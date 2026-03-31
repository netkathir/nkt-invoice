<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <h5 class="bms-list-title mb-0">Client Master</h5>
                    <p class="bms-list-subtitle mb-0">Manage company profiles, contact details, addresses, and country information from one structured client directory.</p>
                </div>
                <div class="bms-list-actions">
                    <button class="btn btn-primary" id="btnAddClient" type="button">Add Client</button>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-panel border-0">
        <div class="bms-list-panel-head">
            <div>
                <div class="bms-list-panel-title">Listing View</div>
                <div class="bms-list-panel-text">Browse all saved client profiles and manage them from a cleaner master list.</div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="dtClients" class="table table-striped table-bordered w-100">
                <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        window.BMS = window.BMS || {};
        window.BMS.initClientMaster && window.BMS.initClientMaster();
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('masters/client_master/form') ?>
<?= $this->endSection() ?>

