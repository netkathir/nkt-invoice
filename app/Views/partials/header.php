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
                if ($p !== '')
                    $initials .= strtoupper(substr($p, 0, 1));
            }
        }
        ?>
        <button class="btn btn-sm app-sidebar-open-trigger" type="button" onclick="toggleSidebar()" aria-label="Toggle sidebar"><span class="app-sidebar-toggle-icon" aria-hidden="true">
            ☰
        </span></button>
        <div class="d-flex align-items-center justify-content-between px-3 py-3">
            <div class="d-flex align-items-center gap-2 min-w-0">
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
                        <span class="header-user-avatar"
                            aria-hidden="true"><?= esc($initials !== '' ? $initials : 'A') ?></span>
                        <span class="header-user-name"><?= esc($adminName !== '' ? $adminName : 'Admin') ?></span>
                    </div>
                    <a class="btn btn-sm btn-logout" href="<?= base_url('admin/logout') ?>">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const STORAGE_KEY = 'sidebarState';
            window.toggleSidebar = function () {
                document.body.classList.remove('bms-sidebar-open');
                document.body.classList.toggle('bms-sidebar-hidden');

                const isHidden = document.body.classList.contains('bms-sidebar-hidden');
                localStorage.setItem(STORAGE_KEY, isHidden ? 'hidden' : 'open');
            };

            function restoreSidebarState() {
                document.body.classList.remove('bms-sidebar-open');
                if (localStorage.getItem(STORAGE_KEY) === 'hidden') {
                    document.body.classList.add('bms-sidebar-hidden');
                }
            }

            document.addEventListener('DOMContentLoaded', restoreSidebarState);
        })();
    </script>
</header>
