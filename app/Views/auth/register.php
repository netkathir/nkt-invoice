<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
    $errors = $errors ?? [];
    $error = $error ?? null;
    $old = $old ?? [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
        <div class="card my-5">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div class="mx-auto mb-2" style="max-width: 220px;">
                        <img src="<?= base_url('assets/img/Netkathir_logo.png') ?>" alt="Company Logo" class="img-fluid">
                    </div>
                    <div class="fw-semibold">Register Admin</div>
                    <div class="small text-muted">This page is available only once during first setup.</div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= esc((string) $error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('admin/register') ?>" class="needs-validation" novalidate>
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc((string) ($old['name'] ?? '')) ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= esc((string) $errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc((string) ($old['email'] ?? '')) ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= esc((string) $errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc((string) ($old['username'] ?? '')) ?>" required>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?= esc((string) $errors['username']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= esc((string) $errors['password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= esc((string) $errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary w-100" type="submit">Create Admin</button>
                            <div class="text-center mt-3">
                                <a class="text-decoration-none" href="<?= base_url('/') ?>">Back to Home</a>
                            </div>
                        </div>
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

