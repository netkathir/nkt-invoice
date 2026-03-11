<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Record System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Student Record System</h1>
        <a href="<?= site_url('student/create') ?>" class="btn btn-primary">Add Student</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form method="get" action="<?= site_url('/') ?>" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" value="<?= esc($query) ?>" class="form-control" placeholder="Search name, roll no, branch, or degree">
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>Sort by ID</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Sort by Name</option>
                <option value="roll_no" <?= $sort === 'roll_no' ? 'selected' : '' ?>>Sort by Roll No</option>
                <option value="branch" <?= $sort === 'branch' ? 'selected' : '' ?>>Sort by Branch</option>
                <option value="degree" <?= $sort === 'degree' ? 'selected' : '' ?>>Sort by Degree</option>
                <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Sort by Created At</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="dir" class="form-select">
                <option value="asc" <?= $dir === 'asc' ? 'selected' : '' ?>>Ascending</option>
                <option value="desc" <?= $dir === 'desc' ? 'selected' : '' ?>>Descending</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Apply</button>
            <a href="<?= site_url('/') ?>" class="btn btn-outline-dark w-100">Reset</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roll No</th>
                    <th>Degree</th>
                    <th>Branch</th>
                    <th>Created At</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">No student records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= esc((string) $student['id']) ?></td>
                            <td><?= esc($student['name']) ?></td>
                            <td><?= esc($student['email']) ?></td>
                            <td><?= esc($student['roll_no']) ?></td>
                            <td><?= esc($student['degree'] ?? '') ?></td>
                            <td><?= esc($student['branch'] ?? '') ?></td>
                            <td><?= esc($student['created_at'] ?? '') ?></td>
                            <td class="text-center">
                                <a href="<?= site_url('student/edit/' . $student['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?= site_url('student/delete/' . $student['id']) ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this student?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <?= $pager->links() ?>
    </div>
</div>
</body>
</html>
