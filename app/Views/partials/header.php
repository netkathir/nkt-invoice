<header class="app-header">
    <div class="app-panel app-panel--header">
        <div class="d-flex align-items-center justify-content-between px-3 py-3">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <?php if (session()->get('admin_id')): ?>
                    <div class="small text-muted text-truncate">Welcome, <?= esc((string) session()->get('admin_name')) ?></div>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="small text-muted d-none d-sm-block"><?= esc(bms_date(date('Y-m-d'))) ?></div>
                <?php if (session()->get('admin_id')): ?>
                    <a class="btn btn-sm btn-outline-danger" href="<?= base_url('admin/logout') ?>">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
