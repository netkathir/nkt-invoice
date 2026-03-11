<?php
    $active = $active ?? '';
    $isActive = static fn (string $key): string => $active === $key ? 'active' : '';
    $mastersOpen = in_array($active, ['client_master'], true);
?>
<aside class="app-sidebar">
    <div class="app-panel app-panel--sidebar">
        <div class="sidebar-top px-3 pt-3 pb-2 d-flex align-items-center justify-content-between gap-2 app-brand">
            <div class="brand-logo-wrap flex-grow-1">
                <img class="brand-logo" src="<?= base_url('assets/img/Netkathir_logo.png') ?>" alt="Netkathir Logo">
            </div>
            <button type="button" class="btn btn-icon btn-sidebar-toggle flex-shrink-0" id="btnToggleSidebar" aria-label="Toggle menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="sidebar-menu px-2 pb-2">
            <div class="nav flex-column nav-pills">
                <a class="nav-link <?= $isActive('dashboard') ?>" href="<?= base_url('dashboard') ?>" data-bms-title="Dashboard">
                    <span class="nav-ico" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M4 13h7V4H4v9Zm9 7h7V11h-7v9ZM4 20h7v-5H4v5Zm9-7h7V4h-7v9Z" fill="currentColor" opacity=".85"/>
                        </svg>
                    </span>
                    <span class="nav-txt">Dashboard</span>
                </a>

                <a class="nav-link nav-parent <?= $mastersOpen ? 'active' : '' ?>" data-bs-toggle="collapse" href="#navMasters" role="button" aria-expanded="<?= $mastersOpen ? 'true' : 'false' ?>" aria-controls="navMasters" data-bms-title="Masters">
                    <span class="nav-ico" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z" fill="currentColor" opacity=".28"/>
                            <path d="M6 20V9.9L12 5.2l6 4.7V20h-3v-6H9v6H6Z" fill="currentColor" opacity=".75"/>
                        </svg>
                    </span>
                    <span class="nav-txt d-flex align-items-center justify-content-between w-100">
                        <span>Masters</span>
                        <span class="nav-caret" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </span>
                </a>
                <div class="collapse <?= $mastersOpen ? 'show' : '' ?>" id="navMasters">
                    <div class="nav flex-column nav-pills nav-sub">
                        <a class="nav-link <?= $isActive('client_master') ?>" href="<?= base_url('masters/client-master') ?>">
                            <span class="nav-ico" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 12c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4Zm0 2c-3.3 0-8 1.7-8 5v1h16v-1c0-3.3-4.7-5-8-5Z" fill="currentColor" opacity=".85"/>
                                </svg>
                            </span>
                            <span class="nav-txt">Client Master</span>
                        </a>
                    </div>
                </div>

                <a class="nav-link <?= $isActive('billable_items') ?>" href="<?= base_url('billable-items') ?>" data-bms-title="Billable Items">
                    <span class="nav-ico" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M7 3h10v2H7V3Zm12 4H5c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2Zm0 12H5V9h14v10Zm-9-8h4v2h-4v-2Zm0 4h8v2h-8v-2Z" fill="currentColor" opacity=".85"/>
                        </svg>
                    </span>
                    <span class="nav-txt">Billable Items</span>
                </a>
            </div>
        </div>
    </div>
</aside>
