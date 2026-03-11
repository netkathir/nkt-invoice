<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <div class="text-center my-5">
            <div class="mx-auto mb-3" style="max-width: 260px;">
                <img src="<?= base_url('assets/img/Netkathir_logo.png') ?>" alt="Company Logo" class="img-fluid">
            </div>
            <h3 class="mb-4">Billing Management System</h3>
            <a class="btn btn-primary btn-lg px-4" href="<?= base_url('admin/login') ?>">Admin Login</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

