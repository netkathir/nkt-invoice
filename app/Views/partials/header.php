<header class="app-header">
    <div class="app-panel app-panel--header">
        <?php
            $adminName = (string) (session()->get('admin_name') ?? '');
            $adminName = trim($adminName);
            $initials = '';
            if ($adminName !== '') {
                $parts = preg_split('/\s+/', $adminName) ?: [];
                foreach (array_slice($parts, 0, 2) as $p) {
                    $p = trim((string) $p);
                    if ($p !== '') $initials .= strtoupper(substr($p, 0, 1));
                }
            }
        ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-3">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button type="button" class="btn p-0 border-0 me-2" id="btnToggleSidebarHeader" onclick="if(window.BMS && window.BMS.toggleSidebarState){ window.BMS.toggleSidebarState(); } else { document.body.classList.toggle('bms-sidebar-hidden'); }" aria-label="Toggle Sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <?php if (session()->get('admin_id')): ?>
                    <div class="header-welcome text-truncate">
                        <span class="text-muted">Welcome back,</span>
                        <span class="fw-semibold"><?= esc($adminName !== '' ? $adminName : 'Admin') ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="small text-muted d-none d-sm-block"><?= esc(bms_date(date('Y-m-d'))) ?></div>
                <?php if (session()->get('admin_id')): ?>
                    <div class="header-user-chip d-none d-sm-inline-flex" title="<?= esc($adminName) ?>">
                        <span class="header-user-avatar" aria-hidden="true"><?= esc($initials !== '' ? $initials : 'A') ?></span>
                        <span class="header-user-name"><?= esc($adminName !== '' ? $adminName : 'Admin') ?></span>
                    </div>
                    <a class="btn btn-sm btn-logout" href="<?= base_url('admin/logout') ?>">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
