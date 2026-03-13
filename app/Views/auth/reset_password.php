<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
    $errors = $errors ?? [];
    $error = $error ?? null;
    $token = $token ?? '';
?>
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <div class="card my-5">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div class="fw-semibold">Reset Password</div>
                    <div class="text-muted small">Choose a new password for your account.</div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= esc((string) $error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('reset-password/' . $token) ?>" class="needs-validation" novalidate>
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="resetPassword" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                            <button class="btn btn-outline-secondary" type="button" id="btnToggleResetPassword" aria-label="Show password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= esc((string) $errors['password']) ?></div>
                        <?php else: ?>
                            <div class="form-text">Minimum 8 chars, with upper, lower, number, and special character.</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="resetConfirmPassword" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                            <button class="btn btn-outline-secondary" type="button" id="btnToggleResetConfirmPassword" aria-label="Show password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= esc((string) $errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Update Password</button>

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

<script>
    (function () {
        function bindToggle(inputId, btnId) {
            const input = document.getElementById(inputId);
            const btn = document.getElementById(btnId);
            if (!input || !btn) return;

            btn.addEventListener('click', function () {
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        }

        bindToggle('resetPassword', 'btnToggleResetPassword');
        bindToggle('resetConfirmPassword', 'btnToggleResetConfirmPassword');
    })();
</script>
<?= $this->endSection() ?>
