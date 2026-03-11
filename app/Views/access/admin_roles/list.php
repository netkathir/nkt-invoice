<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">Admin Role Assignment</h5>
    <a class="btn btn-outline-secondary" href="<?= base_url('dashboard') ?>">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($admins as $a): ?>
                    <?php $aid = (int) ($a['id'] ?? 0); ?>
                    <tr>
                        <td><?= esc((string) $aid) ?></td>
                        <td><?= esc((string) ($a['name'] ?? '')) ?></td>
                        <td><?= esc((string) ($a['username'] ?? '')) ?></td>
                        <td><?= esc((string) ($a['email'] ?? '')) ?></td>
                        <td>
                            <?php $r = $rolesByAdmin[$aid] ?? []; ?>
                            <?php if ($r === []): ?>
                                <span class="text-muted">No roles</span>
                            <?php else: ?>
                                <?php foreach ($r as $rr): ?>
                                    <?php $isSuper = (int) ($rr['is_super'] ?? 0) === 1; ?>
                                    <span class="badge rounded-pill <?= $isSuper ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                                        <?= esc((string) ($rr['name'] ?? '')) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin-roles/' . $aid) ?>">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

