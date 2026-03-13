<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
    $error = $error ?? null;
    $old = $old ?? [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <div class="card my-5">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div class="fw-semibold">Forgot Password</div>
                    <div class="text-muted small">Enter your registered email to receive a reset link.</div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= esc((string) $error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('admin/forgot-password') ?>" class="needs-validation" novalidate>
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= esc((string) ($old['email'] ?? '')) ?>" required>
                        <div class="invalid-feedback">Email Address is required.</div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Send Reset Link</button>

                    <div class="text-center mt-3">
                        <a class="text-decoration-none" href="<?= base_url('admin/login') ?>">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
<?= $this->endSection() ?>

