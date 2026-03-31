<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="bms-list-page">
    <section class="card bms-list-hero border-0">
        <div class="card-body p-4 p-xl-4">
            <div class="bms-list-hero-row">
                <div class="bms-list-copy">
                    <span class="bms-list-kicker">Role Assignment</span>
                    <h5 class="bms-list-title mb-0">Admin Role Assignment</h5>
                    <p class="bms-list-subtitle mb-0">Review assigned roles for each admin account and jump directly into role management.</p>
                </div>
                <div class="bms-list-actions">
                    <a class="btn btn-outline-secondary" href="<?= base_url('dashboard') ?>">Back</a>
                </div>
            </div>
        </div>
    </section>

    <div class="card bms-list-panel border-0">
        <div class="bms-list-panel-head">
            <div>
                <div class="bms-list-panel-kicker">Assignment Register</div>
                <div class="bms-list-panel-title">Listing View</div>
                <div class="bms-list-panel-text">See admins and their current roles in the same unified list pattern.</div>
            </div>
        </div>
        <div class="card-body pt-0">
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
</div>
<?= $this->endSection() ?>

