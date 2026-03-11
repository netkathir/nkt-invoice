<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Add Student</h1>
        <a href="<?= site_url('/') ?>" class="btn btn-outline-secondary">Back to List</a>
    </div>

    <?php $validation = session('validation'); ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= site_url('student/store') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control <?= $validation && $validation->hasError('name') ? 'is-invalid' : '' ?>" value="<?= esc(old('name')) ?>">
                    <?php if ($validation && $validation->hasError('name')): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('name')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control <?= $validation && $validation->hasError('email') ? 'is-invalid' : '' ?>" value="<?= esc(old('email')) ?>">
                    <?php if ($validation && $validation->hasError('email')): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('email')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="roll_no" class="form-label">Roll No <span class="text-danger">*</span></label>
                    <input type="text" id="roll_no" name="roll_no" class="form-control <?= $validation && $validation->hasError('roll_no') ? 'is-invalid' : '' ?>" value="<?= esc(old('roll_no')) ?>">
                    <?php if ($validation && $validation->hasError('roll_no')): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('roll_no')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="degree" class="form-label">Degree <span class="text-danger">*</span></label>
                    <select id="degree" name="degree" class="form-select <?= $validation && $validation->hasError('degree') ? 'is-invalid' : '' ?>">
                        <option value="">Select Degree</option>
                        <?php foreach (['B.E', 'B.Tech', 'M.E', 'M.Tech', 'MBA'] as $degree): ?>
                            <option value="<?= esc($degree) ?>" <?= old('degree') === $degree ? 'selected' : '' ?>><?= esc($degree) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation && $validation->hasError('degree')): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('degree')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                    <select id="branch" name="branch" class="form-select <?= $validation && $validation->hasError('branch') ? 'is-invalid' : '' ?>">
                        <option value="">Select Branch</option>
                        <?php foreach (['CSE', 'IT', 'ECE', 'EEE', 'Mech', 'Civil'] as $branch): ?>
                            <option value="<?= esc($branch) ?>" <?= old('branch') === $branch ? 'selected' : '' ?>><?= esc($branch) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation && $validation->hasError('branch')): ?>
                        <div class="invalid-feedback"><?= esc($validation->getError('branch')) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Save Student</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
