<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Billing Management System') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('favicon_netk.png?v=' . ((@filemtime(FCPATH . 'favicon_netk.png')) ?: 0)) ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('favicon_netk.png?v=' . ((@filemtime(FCPATH . 'favicon_netk.png')) ?: 0)) ?>">
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . ((@filemtime(FCPATH . 'favicon.ico')) ?: 0)) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon_netk.png?v=' . ((@filemtime(FCPATH . 'favicon_netk.png')) ?: 0)) ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . ((@filemtime(FCPATH . 'assets/css/app.css')) ?: 0)) ?>">
</head>
<body>
<div class="app-canvas p-0">
<div class="app-shell app-frame d-flex">
    <?= $this->include('partials/sidebar') ?>
    <button class="btn bms-mobile-sidebar-trigger" type="button" onclick="toggleSidebar()" aria-label="Open sidebar">
        <span aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </span>
    </button>
    <div class="app-sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="app-main flex-grow-1">
        <?= $this->include('partials/header') ?>

        <main class="container-fluid py-3">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>

        <?= $this->include('partials/footer') ?>
    </div>
</div>
    <?= $this->renderSection('modals') ?>


</body>
</html>
