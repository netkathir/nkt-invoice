<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
    $errors = $errors ?? [];
    $error = $error ?? null;
    $old = $old ?? [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <div class="card my-5">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div class="mx-auto mb-2" style="max-width: 220px;">
                        <img src="<?= base_url('assets/img/Netkathir_logo.png') ?>" alt="Company Logo" class="img-fluid">
                    </div>
                    <div class="fw-semibold">Admin Login</div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= esc((string) $error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('admin/login') ?>" class="needs-validation" novalidate>
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>

                    <div class="mb-3">
                        <label class="form-label">Username or Email</label>
                        <input type="text" name="identifier" class="form-control <?= isset($errors['identifier']) ? 'is-invalid' : '' ?>"
                               value="<?= esc((string) ($old['identifier'] ?? '')) ?>" required>
                        <?php if (isset($errors['identifier'])): ?>
                            <div class="invalid-feedback"><?= esc((string) $errors['identifier']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= esc((string) $errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Login</button>
                    <div class="text-center mt-3">
                        <a class="text-decoration-none" href="<?= base_url('/') ?>">Back to Home</a>
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

