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
                        <div class="input-group">
                            <input type="password" name="password" id="loginPassword" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                            <button class="btn btn-outline-secondary" type="button" id="btnTogglePassword" aria-label="Show password">
                                <span id="eyeIcon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= esc((string) $errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Login</button>
                    <div class="text-center mt-3">
                        <a class="text-decoration-none" href="<?= base_url('admin/forgot-password') ?>">Forgot Password?</a>
                    </div>
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

<script>
    (function () {
        const input = document.getElementById('loginPassword');
        const btn = document.getElementById('btnTogglePassword');
        if (!input || !btn) return;

        btn.addEventListener('click', function () {
            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    })();
</script>
<?= $this->endSection() ?>
